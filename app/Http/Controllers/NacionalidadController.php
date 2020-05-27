<?php

namespace App\Http\Controllers;

use App\Models\Nacionalidad;

class NacionalidadController extends Controller
{
    public function __construct()
    {
        //
    }

    public function get($id_empresa)
    {
        $nacionalidades = Nacionalidad::_get($id_empresa);

        return response()->json([
            'message' => sizeof($nacionalidades) === 0 ? 'No se encontraron nacionalidades' : 'Nacionalidades obtenidas',
            'data' => $nacionalidades
        ], sizeof($nacionalidades) === 0 ? 404 : 200);
    }

    public function show($id_empresa, $id_nacionalidad)
    {
        $nacionalidad = Nacionalidad::_show($id_empresa, $id_nacionalidad);

        return response()->json([
            'message' => empty($nacionalidad) ? 'No se encontro nacionalidad' : 'Nacionalidad obtenida',
            'data' => $nacionalidad
        ], empty($nacionalidad) ? 404 : 200);
    }
}
