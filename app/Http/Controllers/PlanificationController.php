<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanificationController extends Controller
{
    public function showUpload(): View
    {
        return view('planification.upload-manifest');
    }

    public function storeManifest(Request $request): RedirectResponse
    {
        $request->validate([
            'manifest' => ['required', 'file', 'max:102400'],
        ]);

        $file = $request->file('manifest');
        $filename = now()->format('YmdHis').'_'.$file->getClientOriginalName();
        $file->storeAs('manifests', $filename, 'local');

        return back()->with('success', 'Manifeste importe avec succes : '.$file->getClientOriginalName());
    }
}
