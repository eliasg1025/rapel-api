<?php

namespace App\Http\Controllers;

use App\Models\Departamento;

class DepartamentosController extends Controller
{
    public function __construct()
    {
        //
    }

    public function get()
    {
        $departamentos = Departamento::_get();

        return response()->json([
            'message' =>  empty($departamentos) ? 'No se encontraron departamentos' : 'Departamentos obtenidos',
            'data' => $departamentos,
        ], empty($departamentos) ? 404 : 200);
    }

    public function show($codigo)
    {
        $departamento = Departamento::_show($codigo);

        return response()->json([
            'message' =>  empty($departamento) ? 'No se encontro el departamento' : 'Departamento obtenido',
            'data' => $departamento,
        ], empty($departamento) ? 404 : 200);
    }

    public function provincias($codigo)
    {
        $departamento = Departamento::_provincias($codigo);

        return response()->json([
            'message' => empty($departamento) ? 'No se encontro el departamento' : 'Departamento obtenido',
            'data' => $departamento
        ], empty($departamento) ? 404 : 200);
    }
}
