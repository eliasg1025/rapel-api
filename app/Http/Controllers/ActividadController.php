<?php


namespace App\Http\Controllers;


use App\Models\Actividad;

class ActividadController extends Controller
{
    public function get($id_empresa)
    {
        $data = Actividad::_get($id_empresa);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
