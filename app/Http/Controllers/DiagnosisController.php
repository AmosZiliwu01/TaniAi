<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Services\GrokAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiagnosisController extends Controller
{
    public function index()
    {
        $history = Diagnosis::where('user_id', Auth::id())->latest()->limit(20)->get();
        return view('diagnosis.index', compact('history'));
    }

    public function analyze(Request $request, GrokAIService $ai)
    {
        $data = $request->validate([
            'crop' => 'required|string',
            'age' => 'nullable|integer',
            'location' => 'nullable|string',
            'humidity' => 'nullable|numeric',
            'weather' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('diagnoses','public');
        }

        $result = $ai->diagnose($data);

        $diag = Diagnosis::create([
            'user_id' => Auth::id(),
            'crop' => $data['crop'],
            'image_path' => $imagePath,
            'disease' => $result['disease'],
            'confidence' => $result['confidence'],
            'risk_level' => $result['risk_level'],
            'description' => $result['description'],
            'recommendations' => $result['recommendations'],
            'status' => 'done',
        ]);

        return redirect()->route('diagnosis.show', $diag);
    }

    public function show(Diagnosis $diagnosis)
    {
        abort_unless($diagnosis->user_id === Auth::id(), 403);
        return view('diagnosis.show', compact('diagnosis'));
    }
}
