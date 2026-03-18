<?php

namespace App\Http\Controllers;

use App\Services\DematEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
