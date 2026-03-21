<?php

namespace App\Http\Controllers;

use App\Models\SuiviVide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RapportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString() ?: null;
        $size = $request->integer('size') ?: 10;
        $page = $request->integer('page') + 1;

        $query = SuiviVide::query()->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('terminal', 'like', '%'.$search.'%')
                    ->orWhere('equipment_number', 'like', '%'.$search.'%')
                    ->orWhere('event_code', 'like', '%'.$search.'%')
                    ->orWhere('event_name', 'like', '%'.$search.'%')
                    ->orWhere('booking_sec_no', 'like', '%'.$search.'%');
            });
        }

        $paginator = $query->paginate($size, ['*'], 'page', $page);

        return response()->json([
            'content' => collect($paginator->items())->map(fn (SuiviVide $r) => $this->toArray($r))->values(),
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

    public function exportExcel(): \Illuminate\Http\Response
    {
        $rows = SuiviVide::query()->orderByDesc('created_at')->get();
        $handle = fopen('php://temp', 'r+');

        // BOM UTF-8 pour une ouverture propre dans Excel.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Terminal',
            'EquipmentNumber',
            'EquipmentTypeSize',
            'EventCode',
            'EventName',
            'EventFamily',
            'EventDate',
            'Booking Sec No',
        ], ';');

        foreach ($rows as $r) {
            fputcsv($handle, [
                $r->terminal ?? '',
                $r->equipment_number ?? '',
                $r->equipment_type_size ?? '',
                $r->event_code ?? '',
                $r->event_name ?? '',
                $r->event_family ?? '',
                $r->event_date ?? '',
                $r->booking_sec_no ?? '',
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="suivi-vides-'.now()->format('YmdHis').'.csv"',
        ]);
    }

    public function destroy(Request $request, SuiviVide $suivi): Response
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

        // Detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

        // Read header
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (! $rawHeader) {
            fclose($handle);
            throw new \RuntimeException('En-tete du fichier introuvable.');
        }

        $header = array_map(fn ($h) => $this->normalizeHeader($h), $rawHeader);
        $columnMap = $this->buildColumnMap($header);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (array_filter($row) === []) {
                continue;
            }
            $data = [];
            foreach ($columnMap as $dbCol => $idx) {
                $data[$dbCol] = isset($row[$idx]) && $row[$idx] !== '' ? $row[$idx] : null;
            }
            $rows[] = $data;

            if (count($rows) >= 200) {
                SuiviVide::query()->insert($rows);
                $rows = [];
            }
        }

        fclose($handle);

        if (! empty($rows)) {
            SuiviVide::query()->insert($rows);
        }

        return SuiviVide::query()->count();
    }

    private function importXlsx(string $path): int
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier XLSX (format invalide).');
        }

        // Namespace OOXML
        $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

        // Shared strings via DOMXPath (gere le namespace)
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

        // Trouver la premiere feuille
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

        $header = null;
        $columnMap = null;
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
                    // Certains fichiers XLSX n'incluent pas l'attribut "r" sur les cellules.
                    // Dans ce cas, on retombe sur l'ordre naturel des cellules de la ligne.
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

            if ($header === null) {
                $header = array_map(fn ($h) => $this->normalizeHeader((string) $h), $rowValues);
                $columnMap = $this->buildColumnMap($header);
                continue;
            }

            if (empty($columnMap)) {
                continue;
            }

            $data = [];
            foreach ($columnMap as $dbCol => $idx) {
                $val = (string) ($rowValues[$idx] ?? '');
                if ($dbCol === 'event_date') {
                    $data[$dbCol] = $this->normalizeEventDate($val);
                    continue;
                }

                $data[$dbCol] = $val !== '' ? $val : null;
            }

            if (array_filter($data) === []) {
                continue;
            }

            $insertRows[] = $data;

            if (count($insertRows) >= 200) {
                SuiviVide::query()->insert($insertRows);
                $insertRows = [];
            }
        }

        if (! empty($insertRows)) {
            SuiviVide::query()->insert($insertRows);
        }

        return SuiviVide::query()->count();
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
        // Remove BOM, trim, lowercase, remove spaces/underscores/dashes
        $h = preg_replace('/^\xEF\xBB\xBF/', '', $header);

        return strtolower(preg_replace('/[\s\-_]+/', '', trim($h)));
    }

    private function buildColumnMap(array $normalizedHeaders): array
    {
        $aliases = [
            'terminal'          => 'terminal',
            'equipmentnumber'   => 'equipment_number',
            'equipmentno'       => 'equipment_number',
            'equipmenttypesize' => 'equipment_type_size',
            'typesize'          => 'equipment_type_size',
            'eventcode'         => 'event_code',
            'eventname'         => 'event_name',
            'eventfamily'       => 'event_family',
            'eventdate'         => 'event_date',
            'bookingsecno'      => 'booking_sec_no',
            'bookingno'         => 'booking_sec_no',
            'bookingsec'        => 'booking_sec_no',
        ];

        $map = [];
        foreach ($normalizedHeaders as $idx => $h) {
            if (isset($aliases[$h])) {
                $map[$aliases[$h]] = $idx;
            }
        }

        return $map;
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role?->name === 'ADMIN', 403);
    }

    private function normalizeEventDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $excelSerial = (float) $value;

            if ($excelSerial > 0) {
                $seconds = (int) round(($excelSerial - 25569) * 86400);

                return gmdate('d/m/Y H:i', $seconds);
            }
        }

        return $value;
    }

    private function toArray(SuiviVide $r): array
    {
        return [
            'id'                => $r->id,
            'terminal'          => $r->terminal,
            'equipmentNumber'   => $r->equipment_number,
            'equipmentTypeSize' => $r->equipment_type_size,
            'eventCode'         => $r->event_code,
            'eventName'         => $r->event_name,
            'eventFamily'       => $r->event_family,
            'eventDate'         => $r->event_date,
            'bookingSecNo'      => $r->booking_sec_no,
        ];
    }
}
