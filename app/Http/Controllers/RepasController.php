<?php

namespace App\Http\Controllers;

use App\Services\DematEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepasController extends Controller
{
    private const RECIPIENTS = [
        'noreplysitedt@gmail.com',
        'iosid242@gmail.com',
    ];

    public function index(): View
    {
        return view('repas.index');
    }

    public function sendMenuDuJour(Request $request, DematEmailService $emailService): RedirectResponse
    {
        $validated = $request->validate([
            'plat1' => ['required', 'string', 'max:255'],
            'plat2' => ['required', 'string', 'max:255'],
        ]);

        try {
            $subject = '[Dakar Terminal] Menu du jour - '.now()->format('d/m/Y');
            $html = $emailService->buildMenuDuJourHtml($validated['plat1'], $validated['plat2']);

            foreach (self::RECIPIENTS as $recipient) {
                $emailService->sendMenuDuJourEmail($recipient, $subject, $html);
            }

            return back()->with('repasSuccess', 'Le menu du jour a ete envoye avec succes.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('repasError', 'Erreur lors de l\'envoi : '.$e->getMessage());
        }
    }
}
