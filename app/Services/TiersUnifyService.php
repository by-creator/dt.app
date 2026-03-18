<?php

namespace App\Services;

use App\Models\TiersUnify;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
