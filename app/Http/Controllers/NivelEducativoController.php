<?php

namespace App\Http\Controllers;

use App\Models\NivelEducativo;

class NivelEducativoController extends Controller
{
    public function __construct()
    {
        //
    }

    public function get($id_empresa)
    {
        $niveles_educativos = NivelEducativo::_get($id_empresa);

        return response()->json([
            'message' => sizeof($niveles_educativos) === 0 ? 'No se encontraron niveles educativos' : 'Niveles educativos obtenidos',
            'data' => $niveles_educativos
        ], sizeof($niveles_educativos) === 0 ? 404 : 200);
    }

    public function show($id_empresa, $id_nivel_educativo)
    {
        $nivel_educativo = NivelEducativo::_show($id_empresa, $id_nivel_educativo);

        return response()->json([
            'message' => empty($nivel_educativo) ? 'No se encontro nivel educativo' : 'Nivel educativo obtenido',
            'data' => $nivel_educativo
        ], empty($nivel_educativo) ? 404 : 200);
    }
}
