<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Services\LlamaAIService;
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

    public function analyze(Request $request, LlamaAIService $ai)
    {
        $data = $request->validate([
            'crop'           => 'required|string|max:100',
            'age'            => 'nullable|integer|min:0|max:9999',
            'soil_condition' => 'nullable|string|max:50',
            'weather'        => 'nullable|string|max:50',
            'image'          => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        if (!$request->hasFile('image')) {
            return back()->withErrors(['image' => 'Foto tanaman wajib diupload untuk diagnosa.'])->withInput();
        }

        $file        = $request->file('image');
        $mime        = $file->getMimeType() ?? 'image/jpeg';
        $rawBytes    = file_get_contents($file->getRealPath());
        $base64      = base64_encode($rawBytes);
        $imgHash     = md5($rawBytes);

        // Check duplicate image
        $existing = Diagnosis::where('user_id', Auth::id())
            ->where('image_hash', $imgHash)
            ->where('crop', $data['crop'])
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route('diagnosis.index')
                ->with('flash_diagnosis_id', $existing->id)
                ->with('status', 'Gambar ini sudah pernah didiagnosa — menampilkan hasil sebelumnya.');
        }

        // Validate: is it a plant image?
        $validation = $ai->validatePlantImage($base64, $mime);
        if (!($validation['is_plant'] ?? true)) {
            return back()->withErrors([
                'image' => 'Gambar yang diupload tidak terdeteksi sebagai tanaman atau bagian tanaman. Silakan upload foto daun, batang, buah, atau tanaman yang bermasalah.',
            ])->withInput();
        }

        // Store image
        $imagePath = $file->store('diagnoses', 'public');

        // Add location from user profile
        $data['location'] = Auth::user()->location ?? '';

        // Run AI diagnosis
        $result = $ai->diagnose($data, $base64, $mime);

        $diag = Diagnosis::create([
            'user_id'         => Auth::id(),
            'crop'            => $data['crop'],
            'image_path'      => $imagePath,
            'image_hash'      => $imgHash,
            'soil_condition'  => $data['soil_condition'] ?? null,
            'disease'         => $result['disease'],
            'confidence'      => $result['confidence'],
            'risk_level'      => $result['risk_level'],
            'plant_part'      => $result['plant_part'] ?? null,
            'description'     => $result['description'],
            'causes'          => $result['causes'] ?? [],
            'solutions'       => $result['solutions'] ?? $result['recommendations'] ?? [],
            'prevention'      => $result['prevention'] ?? [],
            'health_status'   => $result['health_status'] ?? null,
            'recommendations' => $result['recommendations'] ?? [],
            'status'          => 'done',
        ]);

        // Return to index with result flash
        return redirect()->route('diagnosis.index')
            ->with('flash_diagnosis_id', $diag->id);
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

        if (request()->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('status', 'Riwayat diagnosa dihapus.');
    }
}
