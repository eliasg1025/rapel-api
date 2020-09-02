<?php


namespace App\Http\Controllers;

use App\Models\Liquidacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class LiquidacionController extends Controller
{
    public function exportar(Request $request)
    {
        $desde = Carbon::parse($request->query('desde'));
        $hasta = Carbon::parse($request->query('hasta'))->lastOfMonth();

        /*
        return response()->json(
            Liquidacion::select(
                'IdLiquidacion',
                'IdFiniquito',
                'IdEmpresa',
                'RutTrabajador',
                'Mes',
                'Ano',
                DB::raw("CAST(ROUND(MontoAPagar, 2, 0) as decimal(18, 2)) MontoAPagar") ,
                'FechaEmision'
            )->where('IdEmpresa', 9)->where('IdFiniquito', '<>', '0')
                ->whereDate('FechaEmision', '>=', $desde)->whereDate('FechaEmision', '<=', $hasta)->get()
        );*/

        function finiquitosGenerator($desde, $hasta) {
            foreach (
                Liquidacion::select(
                    'IdLiquidacion',
                    'IdFiniquito',
                    'IdEmpresa',
                    'RutTrabajador',
                    'Mes',
                    'Ano',
                    'FechaEmision',
                    DB::raw("CAST(ROUND(MontoAPagar, 2, 0) as decimal(18, 2)) MontoAPagar")
                )->whereIn('IdEmpresa', [9, 14])->where('IdFiniquito', '<>', '0')
                    ->whereDate('FechaEmision', '>=', $desde)->whereDate('FechaEmision', '<=', $hasta)->cursor()
                as $finiquito
            ) {
                yield $finiquito;
            }
        }

        $finiquitos = finiquitosGenerator($desde, $hasta);

        return (new FastExcel($finiquitos))->download('finquitos.csv');
    }

    public function get(Request $request)
    {
        $desde = Carbon::parse($request->query('desde'));
        $hasta = Carbon::parse($request->query('hasta'))->lastOfMonth();
        $empresa_id = $request->query('empresa_id');

        $result = Liquidacion::get($empresa_id, $desde, $hasta);

        return $result;
    }
}
