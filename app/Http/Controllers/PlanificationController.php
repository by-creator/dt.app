<?php

namespace App\Http\Controllers;

use App\Models\Codification;
use App\Services\EdiExporter;
use App\Services\EdiParser;
use App\Services\XlsxExporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlanificationController extends Controller
{
    public function __construct(
        private readonly EdiParser     $parser,
        private readonly EdiExporter   $exporter,
        private readonly XlsxExporter  $xlsxExporter,
    ) {}

    public function showUpload(Request $request): View
    {
        $codifications = null;

        if (auth()->user() !== null) {
            $search         = $request->query('search');
            $codifications  = Codification::latest()
                ->when($search, fn ($q) => $q->where('call_number', 'like', "%{$search}%"))
                ->paginate(10)
                ->withQueryString();
        }

        return view('planification.upload-manifest', compact('codifications'));
    }

    public function storeManifest(Request $request): RedirectResponse
    {
        $request->validate([
            'manifest' => ['required', 'file', 'mimes:txt,text', 'max:51200'],
        ], [
            'manifest.required' => 'Veuillez sélectionner un fichier TXT.',
            'manifest.mimes'    => 'Le fichier doit être au format .txt.',
            'manifest.max'      => 'Le fichier ne doit pas dépasser 50 Mo.',
        ]);

        $uploadedFile = $request->file('manifest');
        $records      = $this->parser->parse($uploadedFile->getRealPath());

        if ($records->isEmpty()) {
            return back()->withInput()
                ->withErrors(['manifest' => 'Aucun enregistrement valide trouvé dans le fichier.']);
        }

        // Récupérer le call_number du premier enregistrement
        $callNumber = trim($records->first()->data['call_number'] ?? '');
        $baseName   = Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp  = now()->format('YmdHis');

        $dir = storage_path('app/codifications');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Stocker le fichier manifest TXT
        $manifestStoreName = "{$timestamp}_{$uploadedFile->getClientOriginalName()}";
        $uploadedFile->storeAs('codifications', $manifestStoreName, 'local');

        // Générer le fichier XLSX
        $xlsxName    = "{$timestamp}_{$baseName}.xlsx";
        $xlsxAbsPath = "{$dir}/{$xlsxName}";
        $headers     = $this->parser->getHeaders();
        $this->xlsxExporter->export($records, $headers, $xlsxAbsPath);

        // Générer le fichier IFTMIN
        $iftminName    = "{$timestamp}_{$baseName}.iftmin";
        $iftminAbsPath = "{$dir}/{$iftminName}";
        $this->exporter->export($records, $iftminAbsPath);

        // Enregistrer dans le modèle Codification
        $codification = Codification::create([
            'call_number' => $callNumber,
            'manifest'    => "codifications/{$manifestStoreName}",
            'xlsx'        => "codifications/{$xlsxName}",
            'iftmin'      => "codifications/{$iftminName}",
        ]);

        return back()
            ->with('success', "Manifeste traité avec succès. Call Number : {$callNumber}")
            ->with('codification_id', $codification->id);
    }

    public function preview(Codification $codification): View
    {
        // Prévisualisation XLSX → tableau HTML
        $xlsxPath   = storage_path("app/{$codification->xlsx}");
        $xlsxRows   = [];
        $xlsxHeaders = [];

        if (file_exists($xlsxPath)) {
            $spreadsheet = IOFactory::load($xlsxPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $data        = $sheet->toArray(null, true, true, false);

            if (! empty($data)) {
                $xlsxHeaders = array_shift($data);
                $xlsxRows    = $data;
            }
        }

        // Prévisualisation IFTMIN → texte brut
        $iftminPath    = storage_path("app/{$codification->iftmin}");
        $iftminContent = file_exists($iftminPath) ? file_get_contents($iftminPath) : '';

        return view('planification.codification-preview', compact(
            'codification',
            'xlsxHeaders',
            'xlsxRows',
            'iftminContent',
        ));
    }

    public function downloadXlsx(Codification $codification): BinaryFileResponse
    {
        $path = storage_path("app/{$codification->xlsx}");

        abort_unless(file_exists($path), 404, 'Fichier XLSX introuvable.');

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadIftmin(Codification $codification): BinaryFileResponse
    {
        $path = storage_path("app/{$codification->iftmin}");

        abort_unless(file_exists($path), 404, 'Fichier IFTMIN introuvable.');

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/edifact',
        ]);
    }

    public function downloadManifest(Codification $codification): BinaryFileResponse
    {
        $path = storage_path("app/{$codification->manifest}");

        abort_unless(file_exists($path), 404, 'Fichier manifeste introuvable.');

        return response()->download($path, basename($path), [
            'Content-Type' => 'text/plain',
        ]);
    }
}
