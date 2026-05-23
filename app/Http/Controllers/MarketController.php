<?php

namespace App\Http\Controllers;

use App\Models\MarketPrice;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        $q = MarketPrice::query();
        if ($request->filled('region')) $q->where('region', $request->region);
        $prices = $q->latest('recorded_at')->limit(30)->get();
        $regions = MarketPrice::select('region')->distinct()->pluck('region');
        return view('market.index', compact('prices','regions'));
    }
}
