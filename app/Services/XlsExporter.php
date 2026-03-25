<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class XlsExporter
{
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = dirname(__DIR__, 2) . '/others/tests/template.xls';
    }

    public function export(Collection $records, array $headers, string $outputPath): string
    {
        $spreadsheet = IOFactory::load($this->templatePath);
        $sheet       = $spreadsheet->getSheet(0);

        // ── Write data rows (starting at row 2, row 1 = template header) ──
        $row = 2;
        foreach ($records as $record) {
            $data = $record->toArray();

            // ── Weight conversion: tonnes → kg ────────────────────────────
            $rawWeight = (float)($data['bl_weight'] ?? 0);
            $data['bl_weight'] = $rawWeight > 0
                ? rtrim(rtrim(number_format($rawWeight * 1000, 2, '.', ''), '0'), '.')
                : '';

            $rawItemWeight = (float)($data['blitem_commodity_weight'] ?? 0);
            $isVehicle = ($data['blitem_yard_item_type'] ?? '') === 'VEHICULE';
            if ($rawItemWeight > 0) {
                $data['blitem_commodity_weight'] = rtrim(rtrim(number_format($rawItemWeight * 1000, 2, '.', ''), '0'), '.');
            } else {
                // Reference always shows 0 for zero-weight vehicles
                $data['blitem_commodity_weight'] = $isVehicle ? '0' : '';
            }

            // ── Volume conversion: internal m³ → formatted m³ ─────────────
            $rawVolume = (float)($data['bl_volume'] ?? 0);
            if ($rawVolume > 0) {
                $data['bl_volume'] = rtrim(rtrim(number_format($rawVolume, 3, '.', ''), '0'), '.');
            } else {
                $data['bl_volume'] = (($data['yard_item_type'] ?? '') === 'CONTENEUR') ? '0' : '';
            }

            $rawItemVol = (float)($data['blitem_commodity_volume'] ?? 0);
            if ($rawItemVol > 0) {
                $data['blitem_commodity_volume'] = rtrim(rtrim(number_format($rawItemVol, 3, '.', ''), '0'), '.');
            } else {
                // Show '0' for structured item types (containers and vehicles), '' otherwise
                $itemType = $data['blitem_yard_item_type'] ?? '';
                $data['blitem_commodity_volume'] = in_array($itemType, ['CONTENEUR', 'VEHICULE']) ? '0' : '';
            }

            // ── Vehicle commodity category (recalculate from individual item weight) ─
            $itemWeightKg = (float)($data['blitem_commodity_weight'] ?? 0);
            if ($isVehicle) {
                $data['blitem_commodity'] = match (true) {
                    $itemWeightKg <= 0     => 'VEH 0-1500Kgs',
                    $itemWeightKg <= 1500  => 'VEH 0-1500Kgs',
                    $itemWeightKg <= 3000  => 'VEH 1501-3000Kgs',
                    $itemWeightKg <= 6000  => 'VEH 3001-6000Kgs',
                    $itemWeightKg <= 9000  => 'VEH 6001-9000Kgs',
                    $itemWeightKg <= 30000 => 'VEH 9001-30000Kgs',
                    default                => 'VEH +30000Kgs',
                };
            }

            // ── Fields empty in XLS output ────────────────────────────────
            $data['consignee']                  = '';
            $data['shipper_name']               = '';
            $data['final_destination_country']  = '';
            $data['transshipment_port_1']       = '';
            $data['transshipment_port_2']       = '';

            // ── Write each column ─────────────────────────────────────────
            $col = 1;
            foreach (array_keys($headers) as $key) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->getCell("{$colLetter}{$row}")->setValue($data[$key] ?? '');
                $col++;
            }

            $row++;
        }

        $writer = new Xls($spreadsheet);
        $writer->save($outputPath);

        return $outputPath;
    }
}
