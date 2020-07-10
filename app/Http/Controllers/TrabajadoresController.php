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

    public function show($dni)
    {
        $trabajador = Trabajador::_show($dni);

        return response()->json([
            'message' => $trabajador ? 'Trabajador obtenido' : 'No se encontro trabajador',
            'data' => $trabajador
        ], $trabajador ? 404 : 200);
    }

    public function info($id_empresa, $dni)
    {
        $trabajador = Trabajador::_info($id_empresa, $dni);

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
}
