<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Services\GrokAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DiagnosisController extends Controller
{
    public function index()
    {
        // Auto-delete riwayat > 2 bulan
        Diagnosis::where('user_id', Auth::id())
            ->where('created_at', '<', Carbon::now()->subMonths(2))
            ->delete();

        $history = Diagnosis::where('user_id', Auth::id())->latest()->limit(20)->get();
        return view('diagnosis.index', compact('history'));
    }

    public function analyze(Request $request, GrokAIService $ai)
    {
        $data = $request->validate([
            'crop'           => 'required|string|max:100',
            'age'            => 'nullable|integer|min:0',
            'location'       => 'nullable|string|max:255',
            'soil_condition' => 'nullable|string',
            'weather'        => 'nullable|string',
            'image'          => 'nullable|image|max:5120',
        ]);

        $imagePath   = null;
        $base64Image = null;
        $mimeType    = 'image/jpeg';

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $mimeType = $file->getMimeType() ?? 'image/jpeg';
            $base64Image = base64_encode(file_get_contents($file->getRealPath()));

            // Validasi apakah gambar adalah tanaman
            $validation = $ai->validatePlantImage($base64Image, $mimeType);
            if (!$validation['is_plant']) {
                return back()->withErrors([
                    'image' => 'Gambar yang diupload bukan gambar tanaman. Silakan upload foto daun, batang, buah, atau bagian tanaman yang ingin didiagnosa.',
                ])->withInput();
            }

            $imagePath = $file->store('diagnoses', 'public');
        }

        $result = $ai->diagnose($data, $base64Image, $mimeType);

        $diag = Diagnosis::create([
            'user_id'         => Auth::id(),
            'crop'            => $data['crop'],
            'image_path'      => $imagePath,
            'disease'         => $result['disease'],
            'confidence'      => $result['confidence'],
            'risk_level'      => $result['risk_level'],
            'description'     => $result['description'],
            'recommendations' => $result['recommendations'],
            'status'          => 'done',
        ]);

        return redirect()->route('diagnosis.show', $diag);
    }

    public function show(Diagnosis $diagnosis)
    {
        abort_unless($diagnosis->user_id === Auth::id(), 403);
        return view('diagnosis.show', compact('diagnosis'));
    }

    public function destroy(Diagnosis $diagnosis)
    {
        abort_unless($diagnosis->user_id === Auth::id(), 403);
        if ($diagnosis->image_path) {
            Storage::disk('public')->delete($diagnosis->image_path);
        }
        $diagnosis->delete();
        return back()->with('status', 'Riwayat diagnosa dihapus.');
    }
}
