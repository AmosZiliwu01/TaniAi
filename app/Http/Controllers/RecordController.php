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
            'crop' => 'required|string',
            'field_name' => 'required|string',
            'area' => 'nullable|numeric',
            'planting_date' => 'nullable|date',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        $data['user_id'] = Auth::id();
        CropRecord::create($data);
        return back()->with('status','Catatan lahan ditambahkan.');
    }

    public function destroy(CropRecord $record)
    {
        abort_unless($record->user_id === Auth::id(), 403);
        $record->delete();
        return back();
    }
}
