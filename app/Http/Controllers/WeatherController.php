<?php

namespace App\Http\Controllers;

use App\Models\WeatherLog;
use Illuminate\Support\Facades\Auth;

class WeatherController extends Controller
{
    public function index()
    {
        $forecast = WeatherLog::latest('forecast_date')->limit(7)->get()->reverse()->values();
        $current = $forecast->first();
        return view('weather.index', compact('forecast','current'));
    }
}
