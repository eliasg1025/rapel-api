<?php

namespace App\Http\Controllers;

use App\Models\Provincia;

class ProvinciasController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show($codigo)
    {
        $provincia = Provincia::_departamento($codigo);

        $conditional = empty($provincia);

        return response()->json([
            'message' => $conditional ? 'No se encontro la provincia' : 'Provincia obtenida',
            'data' => $provincia
        ], $conditional ? 404 : 200);
    }

    public function distritos($codigo)
    {
        $provincia = Provincia::_distritos($codigo);

        return response()->json([
            'message' => empty($provincia) ? 'No se encontro la provincia' : 'Provincia obtenida',
            'data' => $provincia
        ], empty($provincia) ? 404 : 200);
    }
}
