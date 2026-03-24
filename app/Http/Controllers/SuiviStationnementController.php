<?php

namespace App\Http\Controllers;

use App\Models\SuiviStationnement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuiviStationnementController extends Controller
{
    use \App\Http\Controllers\Concerns\StreamsXlsx;
    use \App\Http\Controllers\Concerns\ParsesXlsx;
    public function index(Request $request): JsonResponse
    {
        $size = $request->integer('size') ?: 5;
        $page = $request->integer('page') + 1;

        $query = SuiviStationnement::query()->orderByDesc('created_at');

        foreach ([
            'terminal'    => 'terminal',
            'billingDate' => 'billing_date',
            'shipowner'   => 'shipowner',
            'blNumber'    => 'bl_number',
            'itemNumber'  => 'item_number',
            'itemType'    => 'item_type',
            'type'        => 'type',
            'entryDate'   => 'entry_date',
            'exitDate'    => 'exit_date',
        ] as $param => $column) {
            $val = $request->string($param)->toString() ?: null;
            if ($val !== null) {
                $query->where($column, 'like', '%'.$val.'%');
            }
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
            if (array_filter($row) === [] || $this->isMetaRowStationnement($row)) {
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
        $columnInfo = null;
        $insertRows = [];

        $this->parseXlsx($path, function (array $rowValues) use (&$columnInfo, &$insertRows) {
            if ($this->isMetaRowStationnement($rowValues)) {
                return;
            }

            if ($columnInfo === null) {
                $headers    = array_map(fn ($h) => $this->normalizeHeader((string) $h), $rowValues);
                $columnInfo = $this->buildColumnInfo($headers);

                return;
            }

            if (empty($columnInfo)) {
                return;
            }

            $data = $this->buildRow($rowValues, $columnInfo);
            if ($data === null) {
                return;
            }

            $insertRows[] = $data;

            if (count($insertRows) >= 200) {
                SuiviStationnement::query()->insert($insertRows);
                $insertRows = [];
            }
        });

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
                $data[$dbCol] = $val !== '' ? $this->normalizeToFrenchMonth($val) : null; // normalizeToFrenchMonth returns ?string
            } else {
                $data[$dbCol] = $val !== '' ? $val : null;
            }
        }

        if (array_filter($data) === []) {
            return null;
        }

        return $data;
    }

    private function normalizeToFrenchMonth(string $value): ?string
    {
        if (is_numeric($value)) {
            $excelSerial = (float) $value;
            if ($excelSerial > 0) {
                $seconds = (int) round(($excelSerial - 25569) * 86400);
                return $this->englishMonthToFrench(gmdate('F', $seconds));
            }

            return null;
        }

        $ts = strtotime($value);
        if ($ts !== false) {
            return $this->englishMonthToFrench(date('F', $ts));
        }

        return $this->englishMonthToFrench($value);
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

    private function isMetaRowStationnement(array $rowValues): bool
    {
        $first = strtolower(trim((string) ($rowValues[0] ?? '')));

        return str_starts_with($first, 'aucun filtre')
            || str_starts_with($first, 'filtres')
            || $first === 'total';
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
