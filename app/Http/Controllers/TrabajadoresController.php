<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\Nacionalidad;
use App\Models\NivelEducativo;
use App\Models\TipoVia;
use App\Models\TipoZona;
use App\Models\Trabajador;

class TrabajadoresController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show($id_empresa, $dni)
    {
        $trabajador = Trabajador::where([
            'idEmpresa' => $id_empresa,
            'RutTrabajador' => $dni
        ])->first();

        return response()->json([
            'message' => empty($trabajador) ? 'No se encontro trabajador' : 'Trabajador obtenido',
            'data' => $trabajador
        ], empty($trabajador) ? 404 : 200);
    }

    public function info($id_empresa, $dni)
    {
        $trabajador = Trabajador::_info($id_empresa, $dni);

        return response()->json([
            'message' => empty($trabajador) ? 'No se encontro trabajador' : 'Trabajador obtenido',
            'data' => $trabajador
        ], empty($trabajador) ? 404 : 200);
    }
}
