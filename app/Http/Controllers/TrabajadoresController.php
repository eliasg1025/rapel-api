<?php

namespace App\Http\Controllers;

use App\Models\ActividadTrabajador;
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
        $info_jornal = $request->query('jornal') ? filter_var($request->query('jornal'), FILTER_VALIDATE_BOOLEAN) : false;

        $trabajador = Trabajador::_show($dni, $activo, $info_jornal);

        return response()->json([
            'message' => $trabajador ? 'Trabajador obtenido' : 'No se encontro trabajador',
            'data' => $trabajador
        ], $trabajador ? 200 : 404);
    }

    public function info(Request $request, $dni)
    {
        $activo = $request->query('activo') ? filter_var($request->query('activo'), FILTER_VALIDATE_BOOLEAN) : true;
        $info_jornal = $request->query('jornal') ? filter_var($request->query('jornal'), FILTER_VALIDATE_BOOLEAN) : false;

        $trabajador = Trabajador::_info($dni, $activo, $info_jornal);

        return response()->json([
            'message' => empty($trabajador) ? 'No se encontro trabajador' : 'Trabajador obtenido',
            'data' => $trabajador
        ], empty($trabajador) ? 404 : 200);
    }

    public function infoPeriodos(Request $request, $dni)
    {
        $result = Trabajador::infoPeriodos($dni);

        if ( isset($result['error']) ) {
            return response()->json([
                'message' => 'Error al obtener trabajador',
                'error' => $result['error']
            ], 400);
        }

        if (is_null($result['trabajador'])) {
            return response()->json([
                'message' => 'Trabajador no encontrado',
                'error' => 'Trabajador no encontrado'
            ], 404);
        }


        return response()->json($result);
    }

    public function infoSctr($dni)
    {
        $result = Trabajador::infoSctr($dni);

        if ( isset($result['error']) ) {
            return response()->json([
                'message' => 'Error al obtener trabajador',
                'error' => $result['error']
            ], 400);
        }

        if (is_null($result['trabajador'])) {
            return response()->json([
                'message' => 'Trabajador no encontrado',
                'error' => 'Trabajador no encontrado'
            ], 404);
        }

        return response()->json($result);
    }

    public function getTrabajadoresSctr(Request $request, $empresa_id)
    {
        $fechas = $request->get('fechas');
        $actual = $request->get('actual');
        $result = Trabajador::getTrabajadoresSctr($empresa_id, $request->get('oficios'), $request->get('cuarteles'), $actual, $fechas);

        return response()->json($result);
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

    public function buscarTodos(Request $request)
    {
        $palabra = $request->query('t');
        return Trabajador::buscarTodos($palabra);
    }

    public function test($rut)
    {
        try {
            $actividad = ActividadTrabajador::getUltimoDiaLaborado($rut);
            return response()->json($actividad);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function getActivos( int $empresaId = 0 )
    {
        $result = Trabajador::getActivos($empresaId);
        return response()->json($result);
    }

    public function getPanilla( Request $request, int $empresaId  )
    {
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $result = Trabajador::getPlanilla( $empresaId, $desde, $hasta );
        return response()->json($result);
    }
}
