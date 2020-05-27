<?php

namespace App\Http\Controllers;

use App\Models\TipoVia;

class TipoViaController extends Controller
{
    public function __construct()
    {
        //
    }

    public function get()
    {
        $tipo_vias = TipoVia::all();

        return response()->json([
            'message' => sizeof($tipo_vias) === 0 ? 'No se encontraron tipos de vias' : 'Tipos de vias obtenidos',
            'data' => $tipo_vias
        ], sizeof($tipo_vias) === 0 ? 404 : 200);
    }
}
