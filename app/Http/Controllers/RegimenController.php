<?php


namespace App\Http\Controllers;


use App\Models\Regimen;

class RegimenController extends Controller
{
    public function get()
    {
        $data = Regimen::_all();

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
