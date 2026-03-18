<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class UnifyPrintController extends Controller
{
    public function printFiche(Request $request): View
    {
        return view('facturation.unify-print-template', [
            'data' => $request->all(),
            'type' => "FICHE D'OUVERTURE UNIFY",
            'isAttestation' => false,
            'dateActiviteFormatted' => $this->formatDateFr($request->string('dateActivite')->toString()),
        ]);
    }

    public function printAttestation(Request $request): View
    {
        return view('facturation.unify-print-template', [
            'data' => $request->all(),
            'type' => 'ATTESTATION UNIFY',
            'isAttestation' => true,
            'dateActiviteFormatted' => $this->formatDateFr($request->string('dateActivite')->toString()),
        ]);
    }

    private function formatDateFr(?string $dateIso): string
    {
        if (! $dateIso) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($dateIso)->format('d/m/Y');
        } catch (\Throwable) {
            return $dateIso;
        }
    }
}
