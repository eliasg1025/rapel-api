<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Anticipo;
use App\Models\Planilla;
use Illuminate\Http\Request;

class SueldosController extends Controller
{
    public function getPlanilla(Request $request)
    {
        $empresaId    = $request->get('empresaId');
        $periodo      = $request->get('periodo');
        $zonasLaborId = $request->get('zonasLaborId');
        $tipoPago     = $request->get('tipoPago');

        if ($tipoPago === 'ANTICIPO') {
            $result = Anticipo::get($empresaId, $periodo, $zonasLaborId);
        } else {
            $result = Planilla::get($empresaId, $periodo, $zonasLaborId);
        }

        return response()->json($result);
    }

    public function getDetallePlanilla(Request $request)
    {
        $empresaId    = $request->get('empresaId');
        $periodo      = $request->get('periodo');
        $zonasLaborId = $request->get('zonasLaborId');
        $tipoPago     = $request->get('tipoPago');

        if ($tipoPago === 'ANTICIPO') {
            $result = Anticipo::getDetalle($empresaId, $periodo, $zonasLaborId);
        } else {
            $result = Planilla::getDetalle($empresaId, $periodo, $zonasLaborId);
        }

        return response()->json($result);
    }

    public function getHorasJornal(Request $request)
    {
        $empresaId = $request->get('empresaId');
        $periodo   = $request->get('periodo');
        $regimenId = $request->get('regimenId');

        $result = Planilla::getHorasJornal($empresaId, $periodo, $regimenId);

        return response()->json($result);
    }

    public function getHorasNoJornal(Request $request)
    {
        $empresaId    = $request->get('empresaId');
        $periodo      = $request->get('periodo');
        $tipoPago     = $request->get('tipoPago');

        if ($tipoPago === 'ANTICIPO') {
            $result = Anticipo::getHorasNoJornal($empresaId, $periodo);
        } else {
            $result = Planilla::getHorasNoJornal($empresaId, $periodo);
        }

        return response()->json($result);
    }
}
