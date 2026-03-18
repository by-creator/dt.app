<?php

namespace App\Http\Controllers;

use App\Models\RattachementBl;
use App\Services\DematEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DematFormController extends Controller
{
    public function __construct(
        protected DematEmailService $dematEmailService,
    ) {}

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
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'numeroBl' => ['required', 'string', 'max:100'],
            'maisonTransit' => ['nullable', 'string', 'max:100'],
            'fileBl' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileBadShipping' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileDeclaration' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $submission = $this->createSubmission($request, $validated, 'VALIDATION', 'EN_ATTENTE');

        $this->dematEmailService->sendValidationEmail(
            $submission->nom,
            $submission->prenom,
            $submission->email,
            $submission->bl,
            $submission->maison,
            $request->file('fileBl'),
            $request->file('fileBadShipping'),
            $request->file('fileDeclaration'),
        );

        return $this->successfulResponse($request, 'validation');
    }

    public function submitRemise(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'numeroBl' => ['required', 'string', 'max:100'],
            'maisonTransit' => ['nullable', 'string', 'max:100'],
            'fileDemandeManuscrite' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileBadShipping' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileBl' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileFacture' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fileDeclaration' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $submission = $this->createSubmission($request, $validated, 'REMISE', 'EN_ATTENTE_VALIDATION_FACTURATION');

        $this->dematEmailService->sendRemiseEmail(
            $submission->nom,
            $submission->prenom,
            $submission->email,
            $submission->bl,
            $submission->maison,
            $request->file('fileDemandeManuscrite'),
            $request->file('fileBadShipping'),
            $request->file('fileBl'),
            $request->file('fileFacture'),
            $request->file('fileDeclaration'),
        );

        return $this->successfulResponse($request, 'remise');
    }

    protected function createSubmission(Request $request, array $validated, string $type, string $statut): RattachementBl
    {
        return RattachementBl::query()->create([
            'user_id' => $request->user()?->id,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'bl' => $validated['numeroBl'],
            'maison' => $validated['maisonTransit'] ?? null,
            'statut' => $statut,
            'type' => $type,
            'time_elapsed' => null,
        ]);
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
