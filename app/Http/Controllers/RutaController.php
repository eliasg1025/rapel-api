<?php

namespace App\Http\Controllers;

use App\Models\Ruta;

class RutaController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show($id_empresa, $codigo_troncal, $codigo_ruta)
    {
        $ruta = Ruta::_show($id_empresa, $codigo_troncal, $codigo_ruta);

        $conditional = empty($ruta);

        return response()->json([
            'message' => $conditional ? 'No se encontro la ruta' : 'Ruta obtenida',
            'data' => $ruta
        ], $conditional ? 404 : 200);
    }
}
