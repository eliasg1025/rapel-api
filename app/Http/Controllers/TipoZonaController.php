<?php

namespace App\Http\Controllers;

use App\Models\TipoZona;

class TipoZonaController extends Controller
{
    public function __construct()
    {
        //
    }

    public function get()
    {
        $tipo_zonas = TipoZona::all();

        return response()->json([
            'message' => sizeof($tipo_zonas) === 0 ? 'No se encontraron tipos de zonas' : 'Tipos de zonas obtenidos',
            'data' => $tipo_zonas
        ], sizeof($tipo_zonas) === 0 ? 404 : 200);
    }
}
