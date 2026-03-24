<?php

namespace App\Http\Controllers;

use App\Models\SuiviStationnement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuiviStationnementController extends Controller
{
    use \App\Http\Controllers\Concerns\StreamsXlsx;
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString() ?: null;
        $size = $request->integer('size') ?: 10;
        $page = $request->integer('page') + 1;

        $query = SuiviStationnement::query()->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('terminal', 'like', '%'.$search.'%')
                    ->orWhere('bl_number', 'like', '%'.$search.'%')
                    ->orWhere('shipowner', 'like', '%'.$search.'%')
                    ->orWhere('item_number', 'like', '%'.$search.'%');
            });
        }

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        return response()->json([
            'content' => collect($paginator->items())->map(fn (SuiviStationnement $r) => $this->toArray($r))->values(),
            'page' => $paginator->currentPage() - 1,
            'size' => $paginator->perPage(),
            'totalElements' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'first' => $paginator->onFirstPage(),
            'last' => ! $paginator->hasMorePages(),
        ]);
    }

    public function import(Request $request): Response
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:102400'],
        ]);

        $file = $validated['file'];
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'csv') {
                $count = $this->importCsv($file->getRealPath());
            } elseif ($extension === 'xlsx') {
                $count = $this->importXlsx($file->getRealPath());
            } elseif ($extension === 'xls') {
                return response('Le format XLS n\'est pas pris en charge actuellement. Utilisez un fichier CSV ou XLSX.', 422);
            } else {
                return response('Format non supporte. Utilisez CSV ou XLSX.', 422);
            }

            return response('Import effectue avec succes : '.$count.' ligne(s) importee(s).');
        } catch (\Throwable $e) {
            return response('Erreur lors de l\'import : '.$e->getMessage(), 400);
        }
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->streamXlsx(
            'suivi-stationnements-'.now()->format('YmdHis').'.xlsx',
            ['Terminal', 'BillingDate', 'Shipowner', 'BLNumber', 'Item_Number', 'Item_Type', 'Type', 'EntryDate', 'ExitDate', 'DaysSinceIn'],
            function (callable $write) {
                SuiviStationnement::query()->orderByDesc('created_at')->cursor()->each(function (SuiviStationnement $r) use ($write) {
                    $write([$r->terminal, $r->billing_date, $r->shipowner, $r->bl_number, $r->item_number, $r->item_type, $r->type, $r->entry_date, $r->exit_date, $r->days_since_in]);
                });
            }
        );
    }

    public function destroy(Request $request, SuiviStationnement $suivi): Response
    {
        $this->authorizeAdmin($request);
        $suivi->delete();

        return response()->noContent();
    }

    private function importCsv(string $path): int
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier.');
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (! $rawHeader) {
            fclose($handle);
            throw new \RuntimeException('En-tete du fichier introuvable.');
        }

        $normalizedHeaders = array_map(fn ($h) => $this->normalizeHeader($h), $rawHeader);
        $columnInfo = $this->buildColumnInfo($normalizedHeaders);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (array_filter($row) === []) {
                continue;
            }
            $data = $this->buildRow($row, $columnInfo);
            if ($data !== null) {
                $rows[] = $data;
            }

            if (count($rows) >= 200) {
                SuiviStationnement::query()->insert($rows);
                $rows = [];
            }
        }

        fclose($handle);

        if (! empty($rows)) {
            SuiviStationnement::query()->insert($rows);
        }

        return SuiviStationnement::query()->count();
    }

    private function importXlsx(string $path): int
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier XLSX (format invalide).');
        }

        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

        $sharedStrings = [];
        $ssRaw = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssRaw !== false) {
            $ssDom = new \DOMDocument();
            $ssDom->loadXML($ssRaw, LIBXML_COMPACT | LIBXML_NOWARNING);
            $ssXpath = new \DOMXPath($ssDom);
            $ssXpath->registerNamespace('s', $ns);
            foreach ($ssXpath->query('//s:si') as $si) {
                $tNodes = $ssXpath->query('s:t|s:r/s:t', $si);
                $text = '';
                foreach ($tNodes as $t) {
                    $text .= $t->nodeValue;
                }
                $sharedStrings[] = $text;
            }
        }

        $sheetRaw = $zip->getFromName('xl/worksheets/sheet1.xml')
            ?: $zip->getFromName('xl/worksheets/Sheet1.xml');
        $zip->close();

        if ($sheetRaw === false) {
            throw new \RuntimeException('Feuille de calcul introuvable dans le fichier XLSX.');
        }

        $dom = new \DOMDocument();
        $dom->loadXML($sheetRaw, LIBXML_COMPACT | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('s', $ns);

        $columnInfo = null;
        $insertRows = [];

        foreach ($xpath->query('//s:sheetData/s:row') as $xmlRow) {
            $rowData = [];
            $cellIndex = 0;

            foreach ($xpath->query('s:c', $xmlRow) as $cell) {
                if (! $cell instanceof \DOMElement) {
                    continue;
                }

                $ref = $cell->getAttribute('r');
                if (! empty($ref) && preg_match('/^([A-Z]+)/', $ref, $m)) {
                    $colIdx = $this->colLetterToIndex($m[1]);
                } else {
                    $colIdx = $cellIndex;
                }

                $type = $cell->getAttribute('t');
                $vNode = $xpath->query('s:v', $cell)->item(0);
                $vRaw = $vNode ? $vNode->nodeValue : '';

                if ($type === 's') {
                    $val = $sharedStrings[(int) $vRaw] ?? '';
                } elseif ($type === 'inlineStr') {
                    $tNode = $xpath->query('s:is/s:t', $cell)->item(0);
                    $val = $tNode ? $tNode->nodeValue : '';
                } else {
                    $val = $vRaw;
                }

                $rowData[$colIdx] = $val;
                $cellIndex = $colIdx + 1;
            }

            if (empty($rowData)) {
                continue;
            }

            $maxCol = max(array_keys($rowData));
            for ($i = 0; $i <= $maxCol; $i++) {
                if (! isset($rowData[$i])) {
                    $rowData[$i] = '';
                }
            }
            ksort($rowData);
            $rowValues = array_values($rowData);

            if ($columnInfo === null) {
                $normalizedHeaders = array_map(fn ($h) => $this->normalizeHeader((string) $h), $rowValues);
                $columnInfo = $this->buildColumnInfo($normalizedHeaders);
                continue;
            }

            $data = $this->buildRow($rowValues, $columnInfo);
            if ($data === null) {
                continue;
            }

            $insertRows[] = $data;

            if (count($insertRows) >= 200) {
                SuiviStationnement::query()->insert($insertRows);
                $insertRows = [];
            }
        }

        if (! empty($insertRows)) {
            SuiviStationnement::query()->insert($insertRows);
        }

        return SuiviStationnement::query()->count();
    }

    /**
     * Build a simple column index map from normalized headers.
     *
     * Handles data(3).xlsx structure:
     *   TerminalName | BilingDateTime - Month | Shipowner | BLNumber |
     *   Item_Number  | Item_Type              | Type      |
     *   EntryDate - Month | ExitDate - Month  | DaysSinceIn
     */
    private function buildColumnInfo(array $normalizedHeaders): array
    {
        $aliases = [
            'terminalname'          => 'terminal',
            'terminal'              => 'terminal',
            'shipowner'             => 'shipowner',
            'blnumber'              => 'bl_number',
            'bl'                    => 'bl_number',
            'itemnumber'            => 'item_number',
            'itemtype'              => 'item_type',
            'type'                  => 'type',
            // "BilingDateTime - Month" normalizes to "bilingdatetimemonth"
            'bilingdatetimemonth'   => 'billing_date',
            'billingdatetimemonth'  => 'billing_date',
            'billingdatemonth'      => 'billing_date',
            'bilingdatemonth'       => 'billing_date',
            // "EntryDate - Month" normalizes to "entrydatemonth"
            'entrydatemonth'        => 'entry_date',
            'entrydate'             => 'entry_date',
            // "ExitDate - Month" normalizes to "exitdatemonth"
            'exitdatemonth'         => 'exit_date',
            'exitdate'              => 'exit_date',
            // "DaysSinceIn" normalizes to "dayssincein"
            'dayssincein'           => 'days_since_in',
            'sommedayssincein'      => 'days_since_in',
            'totaldayssincein'      => 'days_since_in',
        ];

        $map = [];
        foreach ($normalizedHeaders as $idx => $h) {
            if (isset($aliases[$h]) && ! isset($map[$aliases[$h]])) {
                $map[$aliases[$h]] = $idx;
            }
        }

        return $map;
    }

    private function buildRow(array $rowValues, array $columnMap): ?array
    {
        $monthCols = ['billing_date', 'entry_date', 'exit_date'];
        $data = [];

        foreach ($columnMap as $dbCol => $idx) {
            $val = (string) ($rowValues[$idx] ?? '');
            if ($dbCol === 'days_since_in') {
                $data[$dbCol] = is_numeric($val) ? (float) $val : null;
            } elseif (in_array($dbCol, $monthCols, true)) {
                $data[$dbCol] = $val !== '' ? $this->englishMonthToFrench($val) : null;
            } else {
                $data[$dbCol] = $val !== '' ? $val : null;
            }
        }

        if (array_filter($data) === []) {
            return null;
        }

        return $data;
    }

    private function englishMonthToFrench(string $month): string
    {
        return [
            'January'   => 'Janvier',
            'February'  => 'Fevrier',
            'March'     => 'Mars',
            'April'     => 'Avril',
            'May'       => 'Mai',
            'June'      => 'Juin',
            'July'      => 'Juillet',
            'August'    => 'Aout',
            'September' => 'Septembre',
            'October'   => 'Octobre',
            'November'  => 'Novembre',
            'December'  => 'Decembre',
        ][$month] ?? $month;
    }

    private function colLetterToIndex(string $col): int
    {
        $index = 0;
        for ($i = 0, $len = strlen($col); $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - 64);
        }

        return $index - 1;
    }

    private function normalizeHeader(string $header): string
    {
        $h = preg_replace('/^\xEF\xBB\xBF/', '', $header);

        return strtolower(preg_replace('/[\s\-_]+/', '', trim($h)));
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role?->name === 'ADMIN', 403);
    }

    private function toArray(SuiviStationnement $r): array
    {
        return [
            'id'          => $r->id,
            'terminal'    => $r->terminal,
            'billingDate' => $r->billing_date,
            'shipowner'   => $r->shipowner,
            'blNumber'    => $r->bl_number,
            'itemNumber'  => $r->item_number,
            'itemType'    => $r->item_type,
            'type'        => $r->type,
            'entryDate'   => $r->entry_date,
            'exitDate'    => $r->exit_date,
            'daysSinceIn' => $r->days_since_in,
        ];
    }
}
