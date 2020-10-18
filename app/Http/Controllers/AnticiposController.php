<?php


namespace App\Http\Controllers;


use App\Models\Anticipo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class AnticiposController extends Controller
{
    public function get(Request $request)
    {
        $empresaId   = $request->get('empresaId');
        $periodo     = $request->get('periodo');
        $zonaLaborId = $request->get('zonasLaborId');

        $result = Anticipo::trabajadorAnticipos(
            $empresaId,
            $periodo,
            $zonaLaborId
        );
        //return response()->json($result);
        return (new FastExcel($result))->download('trabajador.csv');
    }

    public function getHorasSinDigitacion (Request $request)
    {
        $empresaId   = $request->get('empresaId');
        $periodo     = $request->get('periodo');
        $zonasLaborId = $request->get('zonasLaborId');

        $result = Anticipo::trabajadorHorasSinDigitacionGenerator(
            $empresaId,
            $periodo,
            $zonasLaborId
        );
        //return response()->json($result);
        return (new FastExcel($result))->download('horasSinDigitacion.csv');
    }

    public function getHorasConDigitacion (Request $request)
    {
        $empresaId   = $request->get('empresaId');
        $periodo     = $request->get('periodo');
        $zonasLaborId = $request->get('zonasLaborId');

        $result = Anticipo::trabajadorHorasConDigitacionGenerator(
            $empresaId,
            $periodo,
            $zonasLaborId
        );
        //return response()->json($result);
        return (new FastExcel($result))->download('horasConDigitacion.csv');
    }
}
