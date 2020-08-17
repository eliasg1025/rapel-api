<?php


namespace App\Http\Controllers;

use App\Models\Liquidacion;
use Rap2hpoutre\FastExcel\FastExcel;

class LiquidacionController extends Controller
{
    public function exportar()
    {
        function finiquitosGenerator() {
            foreach (
                Liquidacion::select('IdLiquidacion', 'IdFiniquito', 'IdEmpresa', 'RutTrabajador', 'Mes', 'Ano', 'MontoAPagar')
                    ->whereIn('IdEmpresa', ['9', '14'])->where('IdFiniquito', '<>', '0')->cursor()
                as $finiquito
            ) {
                yield $finiquito;
            }
        }

        $finiquitos = finiquitosGenerator();
        return (new FastExcel($finiquitos))->export('file.csv');
    }
}
