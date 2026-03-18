<?php

namespace App\Http\Controllers;

use App\Models\TiersUnify;
use App\Services\TiersUnifyService;
use Illuminate\Http\Request;

class TiersUnifyController extends Controller
{
    public function __construct(
        protected TiersUnifyService $service,
    ) {}

    public function save(Request $request)
    {
        $validated = $request->validate([
            'raisonSociale' => ['required', 'string', 'max:255'],
            'compteIpaki' => ['required', 'string', 'max:50'],
            'compteNeptune' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $saved = $this->service->saveTiers($validated);

            return response()->json($this->toArray($saved));
        } catch (\Throwable $exception) {
            return response('Erreur lors de l\'enregistrement : '.$exception->getMessage(), 400);
        }
    }

    public function index(Request $request)
    {
        $page = $this->service->listTiers(
            search: $request->string('search')->toString() ?: null,
            page: $request->integer('page'),
            size: $request->integer('size') ?: 10,
        );

        return response()->json([
            'content' => collect($page->items())->map(fn (TiersUnify $tiers) => $this->toArray($tiers))->values(),
            'page' => $page->currentPage() - 1,
            'size' => $page->perPage(),
            'totalElements' => $page->total(),
            'totalPages' => $page->lastPage(),
            'first' => $page->onFirstPage(),
            'last' => ! $page->hasMorePages(),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $this->authorizeAdmin($request);

        $csv = collect($this->service->findAll())
            ->prepend((object) [
                'raison_sociale' => 'raisonSociale',
                'compte_ipaki' => 'compteIpaki',
                'compte_neptune' => 'compteNeptune',
            ])
            ->map(fn ($item) => collect([
                $item->raison_sociale,
                $item->compte_ipaki,
                $item->compte_neptune,
            ])->map(function ($value) {
                $value = (string) ($value ?? '');
                $value = str_replace('"', '""', $value);

                return '"'.$value.'"';
            })->implode(';'))
            ->implode("\n");

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tiers-unify-'.now()->format('YmdHis').'.csv"',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeAdmin($request);

        $rows = collect($this->service->findAll())
            ->map(fn (TiersUnify $tiers) => '<tr><td>'.e($tiers->raison_sociale).'</td><td>'.e($tiers->compte_ipaki).'</td><td>'.e($tiers->compte_neptune ?? '').'</td></tr>')
            ->implode('');

        $html = '<table><thead><tr><th>raisonSociale</th><th>compteIpaki</th><th>compteNeptune</th></tr></thead><tbody>'.$rows.'</tbody></table>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="tiers-unify-'.now()->format('YmdHis').'.xls"',
        ]);
    }

    public function import(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $validated['file'];
        $fileName = strtolower($file->getClientOriginalName() ?? '');

        if (! str_ends_with($fileName, '.csv')) {
            return response('Format non supporte pour le moment. Utilisez un fichier .csv.', 400);
        }

        $content = (string) file_get_contents($file->getRealPath());
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $items = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($index === 0 && str_contains(strtolower($line), 'raisonsociale')) {
                continue;
            }

            $columns = preg_split('/[;,]/', $line) ?: [];
            if (count($columns) < 3) {
                return response('Ligne invalide: '.($index + 1), 400);
            }

            $items[] = [
                'raisonSociale' => trim($columns[0]),
                'compteIpaki' => trim($columns[1]),
                'compteNeptune' => trim($columns[2]) ?: null,
            ];
        }

        $this->service->saveAll($items);

        return response('Import effectue: '.count($items).' tiers.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role?->name === 'ADMIN', 403);
    }

    private function toArray(TiersUnify $tiers): array
    {
        return [
            'id' => $tiers->id,
            'raisonSociale' => $tiers->raison_sociale,
            'compteIpaki' => $tiers->compte_ipaki,
            'compteNeptune' => $tiers->compte_neptune,
            'createdAt' => $tiers->created_at?->toIso8601String(),
        ];
    }
}
