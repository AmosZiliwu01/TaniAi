<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\MarketPrice;
use App\Models\WeatherLog;
use App\Models\CommunityPost;
use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = [
            'diagnoses' => Diagnosis::where('user_id',$user->id)->count(),
            'healthy_rate' => 87,
            'active_alerts' => 3,
            'predicted_yield' => 80,
        ];
        $recent_diagnoses = Diagnosis::where('user_id',$user->id)->latest()->limit(4)->get();
        $market = MarketPrice::latest('recorded_at')->limit(6)->get();
        $weather = WeatherLog::latest('forecast_date')->limit(7)->get();
        $recommendations = Recommendation::where('user_id',$user->id)->latest()->limit(4)->get();
        $posts = CommunityPost::with('user')->latest()->limit(4)->get();
        return view('dashboard.index', compact('stats','recent_diagnoses','market','weather','recommendations','posts'));
    }
}
