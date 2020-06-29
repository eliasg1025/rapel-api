<?php


namespace App\Http\Controllers;


use App\Models\Agrupacion;

class AgrupacionController extends Controller
{
    public function get($id_empresa)
    {
        $data = Agrupacion::_get($id_empresa);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
