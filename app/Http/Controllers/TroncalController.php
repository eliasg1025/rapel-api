<?php

namespace App\Http\Controllers;

use App\Models\Troncal;

class TroncalController extends Controller
{
    public function __construct()
    {
        //
    }

    public function get($id_empresa)
    {
        $troncales = Troncal::_get($id_empresa);

        return response()->json([
            'message' => sizeof($troncales) === 0 ? 'No se encontraron troncales' : 'Troncales obtenidos',
            'data' => $troncales
        ], sizeof($troncales) === 0 ? 404 : 200);
    }

    public function show($id_empresa, $codigo)
    {
        $troncal = Troncal::_show($id_empresa, $codigo);

        return response()->json([
            'message' => empty($troncal) ? 'No se encontro troncal' : 'Troncal obtenido',
            'data' => $troncal
        ], empty($troncal) ? 404 : 200);
    }

    public function rutas($id_empresa, $codigo)
    {
        $troncal = Troncal::_rutas($id_empresa, $codigo);

        return response()->json([
            'message' => empty($troncal) ? 'No se encontro troncal' : 'Troncal obtenido',
            'data' => $troncal
        ], empty($troncal) ? 404 : 200);
    }
}
