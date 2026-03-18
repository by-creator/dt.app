<?php

namespace App\Services;

use App\Models\TiersUnify;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TiersUnifyService
{
    public function saveTiers(TiersUnify|array $data): TiersUnify
    {
        $payload = $data instanceof TiersUnify ? $data->toArray() : $data;

        return TiersUnify::query()->create([
            'raison_sociale' => strtoupper(trim((string) ($payload['raison_sociale'] ?? $payload['raisonSociale'] ?? ''))),
            'compte_ipaki' => trim((string) ($payload['compte_ipaki'] ?? $payload['compteIpaki'] ?? '')),
            'compte_neptune' => $this->nullableString($payload['compte_neptune'] ?? $payload['compteNeptune'] ?? null),
            'created_at' => now(),
        ]);
    }

    public function listTiers(?string $search, int $page, int $size): LengthAwarePaginator
    {
        return TiersUnify::query()
            ->when($search, function ($query, $search) {
                $term = '%'.$search.'%';

                $query->where(function ($builder) use ($term) {
                    $builder->where('raison_sociale', 'like', $term)
                        ->orWhere('compte_ipaki', 'like', $term)
                        ->orWhere('compte_neptune', 'like', $term);
                });
            })
            ->orderBy('raison_sociale')
            ->paginate(
                perPage: max(1, $size),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page + 1),
            );
    }

    public function findAll(): \Illuminate\Support\Collection
    {
        return TiersUnify::query()->orderBy('raison_sociale')->get();
    }

    public function saveAll(iterable $items): void
    {
        foreach ($items as $item) {
            $this->saveTiers($item instanceof TiersUnify ? $item : (array) $item);
        }
    }

    public function importCsvWithLocalInfile(UploadedFile $file): int
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'csv') {
            throw new \InvalidArgumentException('Format non supporte. Utilisez un fichier .csv pour l\'import massif.');
        }

        $delimiter = $this->detectCsvDelimiter($file->getRealPath());
        $temporaryDirectory = storage_path('app/unify-imports');
        File::ensureDirectoryExists($temporaryDirectory);

        $temporaryPath = $temporaryDirectory.'/'.uniqid('tiers-unify-', true).'.csv';
        File::copy($file->getRealPath(), $temporaryPath);

        try {
            $path = str_replace('\\', '\\\\', $temporaryPath);
            $separator = $delimiter === ',' ? ',' : ';';

            $sql = <<<SQL
LOAD DATA LOCAL INFILE '{$path}'
INTO TABLE tiers_unify
CHARACTER SET utf8mb4
FIELDS TERMINATED BY '{$separator}'
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@raison_sociale, @compte_ipaki, @compte_neptune)
SET
    raison_sociale = UPPER(TRIM(REPLACE(@raison_sociale, '\r', ''))),
    compte_ipaki = TRIM(REPLACE(@compte_ipaki, '\r', '')),
    compte_neptune = NULLIF(TRIM(REPLACE(@compte_neptune, '\r', '')), ''),
    created_at = NOW()
SQL;

            DB::connection()->getPdo()->exec($sql);

            return (int) (DB::selectOne('SELECT ROW_COUNT() AS count')->count ?? 0);
        } finally {
            File::delete($temporaryPath);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function detectCsvDelimiter(string $path): string
    {
        $handle = fopen($path, 'rb');
        $header = $handle ? (string) fgets($handle) : '';
        if (is_resource($handle)) {
            fclose($handle);
        }

        return substr_count($header, ';') >= substr_count($header, ',') ? ';' : ',';
    }
}
