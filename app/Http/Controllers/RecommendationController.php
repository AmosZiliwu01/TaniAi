<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function index()
    {
        $items = Recommendation::where('user_id', Auth::id())->latest()->get();
        return view('recommendations.index', compact('items'));
    }
}
