<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Liquidacion extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Liquidacion';

    public $incrementing = false;

    public static function get($empresa_id, $desde, $hasta)
    {
        $finiquitos = Liquidacion::select(
            'IdLiquidacion',
            'IdFiniquito',
            'IdEmpresa',
            'RutTrabajador',
            'Mes',
            'Ano',
            'FechaEmision',
            DB::raw("CAST(ROUND(MontoAPagar, 2, 0) as decimal(18, 2)) MontoAPagar")
        )
            ->where('IdEmpresa', $empresa_id)->where('IdFiniquito', '<>', '0')
            ->whereDate('FechaEmision', '>=', $desde)->whereDate('FechaEmision', '<=', $hasta)
            ->get();

        return $finiquitos;
    }
}
