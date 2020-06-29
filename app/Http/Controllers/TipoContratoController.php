<?php


namespace App\Http\Controllers;


use App\Models\TipoContrato;

class TipoContratoController extends Controller
{
    public function get($id_empresa)
    {
        $data = TipoContrato::_get($id_empresa);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
