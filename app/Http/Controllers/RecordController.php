<?php

namespace App\Http\Controllers;

use App\Models\CropRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecordController extends Controller
{
    public function index()
    {
        $records = CropRecord::where('user_id', Auth::id())->latest()->get();
        return view('records.index', compact('records'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'crop'          => 'required|string|max:100',
            'field_name'    => 'required|string|max:255',
            'area'          => 'nullable|numeric|min:0|max:99999',
            'planting_date' => 'nullable|date',
            'status'        => 'required|string|max:100',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $data['user_id'] = Auth::id();
        CropRecord::create($data);
        return back()->with('status', 'Catatan lahan berhasil ditambahkan.');
    }

    public function destroy(CropRecord $record)
    {
        abort_unless($record->user_id === Auth::id(), 403);
        $record->delete();
        return back()->with('status', 'Catatan dihapus.');
    }
}
