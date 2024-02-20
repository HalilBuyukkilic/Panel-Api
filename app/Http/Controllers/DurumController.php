<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Durum;

class DurumController extends Controller
{
    public function index(){

        $durum=Durum::all();
        return  response()->json($durum);
    }
}
