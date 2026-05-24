<?php

namespace App\Http\Controllers;

class CultivationController extends Controller
{
    public function index()
    {
        $crops = ['Padi','Jagung','Cabai','Tomat','Kedelai','Bawang Merah','Kentang','Kopi','Kakao'];
        return view('cultivation.index', compact('crops'));
    }
}
