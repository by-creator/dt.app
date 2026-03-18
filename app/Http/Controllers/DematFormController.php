<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DematFormController extends Controller
{
    public function validationForm(): View
    {
        return view('demat.validation');
    }

    public function remiseForm(): View
    {
        return view('demat.remise');
    }

    public function submitValidation(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'numeroBl' => ['nullable', 'string', 'max:255'],
            'maisonTransit' => ['nullable', 'string', 'max:255'],
            'fileBl' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileBadShipping' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileDeclaration' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $this->storeSubmission('validation', $request, $validated, [
            'fileBl',
            'fileBadShipping',
            'fileDeclaration',
        ]);

        return $this->successfulResponse($request, 'validation');
    }

    public function submitRemise(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'numeroBl' => ['nullable', 'string', 'max:255'],
            'maisonTransit' => ['nullable', 'string', 'max:255'],
            'fileDemandeManuscrite' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileBadShipping' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileBl' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileFacture' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileDeclaration' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $this->storeSubmission('remise', $request, $validated, [
            'fileDemandeManuscrite',
            'fileBadShipping',
            'fileBl',
            'fileFacture',
            'fileDeclaration',
        ]);

        return $this->successfulResponse($request, 'remise');
    }

    protected function storeSubmission(string $type, Request $request, array $validated, array $fileFields): void
    {
        $submissionId = now()->format('YmdHis').'-'.Str::lower(Str::random(8));
        $basePath = "demat/{$type}/{$submissionId}";
        $storedFiles = [];

        foreach ($fileFields as $fileField) {
            if (! $request->hasFile($fileField)) {
                continue;
            }

            $storedFiles[$fileField] = $request->file($fileField)->store($basePath, 'local');
        }

        Storage::disk('local')->put(
            "{$basePath}/submission.json",
            json_encode([
                'id' => $submissionId,
                'type' => $type,
                'submitted_at' => now()->toIso8601String(),
                'data' => array_diff_key($validated, array_flip($fileFields)),
                'files' => $storedFiles,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function successfulResponse(Request $request, string $type): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'type' => $type,
            ]);
        }

        return redirect("/demat/{$type}?success=true");
    }
}
