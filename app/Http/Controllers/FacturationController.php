<?php

namespace App\Http\Controllers;

use App\Models\RattachementBl;
use App\Services\DematEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FacturationController extends Controller
{
    public function __construct(
        protected DematEmailService $dematEmailService,
    ) {}

    public function ies(): View
    {
        return view('facturation.ies');
    }

    public function validations(Request $request): View
    {
        $this->authorizeFacturationAccess($request);

        $items = RattachementBl::query()
            ->where('type', 'VALIDATION')
            ->latest()
            ->get();

        $mapped = $items->map(fn (RattachementBl $item) => $this->mapRattachement($item))->values();

        return view('facturation.validations', [
            'initialDemandes' => $mapped,
        ]);
    }

    public function remises(Request $request): View
    {
        $this->authorizeFacturationAccess($request);

        $roleName = $request->user()?->role?->name;
        $initialStatut = '';

        if ($roleName === 'FACTURATION') {
            $initialStatut = 'EN_ATTENTE_VALIDATION_FACTURATION';
        } elseif (in_array($roleName, ['DIRECTION_GENERALE', 'DIRECTION_FINANCIERE', 'DIRECTION_EXPLOITATION'], true)) {
            $initialStatut = 'EN_ATTENTE_VALIDATION_DIRECTION';
        }

        $items = RattachementBl::query()
            ->where('type', 'REMISE')
            ->when(
                $initialStatut !== '',
                fn ($builder) => $builder->where('statut', $initialStatut)
            )
            ->latest()
            ->get();

        $items = $this->filterRemisesForRole($roleName, $items);
        $mapped = $items->map(fn (RattachementBl $item) => $this->mapRattachement($item))->values();

        return view('facturation.remises', [
            'initialRemises' => $mapped,
            'initialRemiseStatut' => $initialStatut,
        ]);
    }

    public function sendIesAccessLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100'],
        ]);

        try {
            $this->dematEmailService->sendIesAccessLinkEmail(
                $validated['email'],
                url('/demat'),
            );

            return redirect()
                ->route('facturation.ies', ['tab' => 'lien-acces'])
                ->with('iesSuccess', "Lien d'acces envoye avec succes a {$validated['email']}.");
        } catch (\Throwable) {
            return redirect()
                ->route('facturation.ies', ['tab' => 'lien-acces'])
                ->with('iesError', "Erreur lors de l'envoi du mail.");
        }
    }

    public function sendIesAccountCreated(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:100'],
        ]);

        try {
            $this->dematEmailService->sendIesAccountCreatedEmail(
                $validated['email'],
                $validated['password'],
                url('/demat'),
            );

            return redirect()
                ->route('facturation.ies', ['tab' => 'creation-compte'])
                ->with('iesSuccess', "Email de creation de compte envoye a {$validated['email']}.");
        } catch (\Throwable) {
            return redirect()
                ->route('facturation.ies', ['tab' => 'creation-compte'])
                ->with('iesError', "Erreur lors de l'envoi du mail.");
        }
    }

    public function sendIesPasswordReset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:100'],
        ]);

        try {
            $this->dematEmailService->sendIesPasswordResetEmail(
                $validated['email'],
                $validated['password'],
                url('/demat'),
            );

            return redirect()
                ->route('facturation.ies', ['tab' => 'reset-password'])
                ->with('iesSuccess', "Email de reinitialisation envoye a {$validated['email']}.");
        } catch (\Throwable) {
            return redirect()
                ->route('facturation.ies', ['tab' => 'reset-password'])
                ->with('iesError', "Erreur lors de l'envoi du mail.");
        }
    }

    private function authorizeFacturationAccess(Request $request): void
    {
        abort_unless(
            in_array($request->user()?->role?->name, ['FACTURATION', 'DIRECTION_GENERALE', 'DIRECTION_FINANCIERE', 'DIRECTION_EXPLOITATION', 'ADMIN', 'SUPER_U'], true),
            403
        );
    }

    private function filterRemisesForRole(?string $roleName, Collection $items): Collection
    {
        if ($roleName === 'FACTURATION') {
            return $items->where('statut', 'EN_ATTENTE_VALIDATION_FACTURATION');
        }

        if (in_array($roleName, ['DIRECTION_GENERALE', 'DIRECTION_FINANCIERE', 'DIRECTION_EXPLOITATION'], true)) {
            return $items->where('statut', 'EN_ATTENTE_VALIDATION_DIRECTION');
        }

        return $items;
    }

    private function mapRattachement(RattachementBl $item): array
    {
        return [
            'id' => $item->id,
            'nom' => $item->nom,
            'prenom' => $item->prenom,
            'email' => $item->email,
            'bl' => $item->bl,
            'maisonTransit' => $item->maison,
            'type' => $item->type,
            'statut' => $item->statut,
            'motifRejet' => $item->motif_rejet,
            'pourcentage' => $item->pourcentage,
            'createdAt' => $item->created_at?->toIso8601String(),
            'updatedAt' => $item->updated_at?->toIso8601String(),
        ];
    }
}
