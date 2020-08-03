<?php

namespace App\Http\Controllers;

use App\Models\Trabajador;
use Illuminate\Http\Request;

class TrabajadoresController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show(Request $request, $dni)
    {
        $activo = $request->query('activo') ? filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN) : true;
        $trabajador = Trabajador::_show($dni, $activo);

        return response()->json([
            'message' => $trabajador ? 'Trabajador obtenido' : 'No se encontro trabajador',
            'data' => $trabajador
        ], $trabajador ? 200 : 404);
    }

    public function info(Request $request, $dni)
    {
        $activo = $request->query('activo') ? filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN) : true;
        $trabajador = Trabajador::_info($dni, $activo);

        return response()->json([
            'message' => empty($trabajador) ? 'No se encontro trabajador' : 'Trabajador obtenido',
            'data' => $trabajador
        ], empty($trabajador) ? 404 : 200);
    }

    public function revision(Request $request)
    {
        $result = Trabajador::revision($request->trabajadores);
        return response()->json($result);
    }

    public function revisionSinTrabajadores(Request $request)
    {
        $result = Trabajador::revision($request->trabajadores, false);
        return response()->json($result);
    }

    public function buscar(Request $request)
    {
        $palabra = $request->query('t');
        return Trabajador::buscar($palabra);
    }
}
