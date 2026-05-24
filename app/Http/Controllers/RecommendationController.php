<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function index()
    {
        try {
            $items = Recommendation::where('user_id', Auth::id())->latest()->get();
        } catch (\Throwable $e) {
            $items = collect();
        }
        return view('recommendations.index', compact('items'));
    }
}
