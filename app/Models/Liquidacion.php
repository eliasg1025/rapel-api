<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Liquidacion extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Liquidacion';

    public $incrementing = false;

    public static function get(int $empresa_id, $desde, $hasta)
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
            ->where('IdFiniquito', '<>', '0')
            ->whereDate('FechaEmision', '>=', $desde)->whereDate('FechaEmision', '<=', $hasta)
            ->when($empresa_id === 0, function($query) {
                $query->whereIn('IdEmpresa', [9, 14]);
            })
            ->when($empresa_id !== 0, function($query) use ($empresa_id) {
                $query->where('IdEmpresa', $empresa_id);
            })
            ->get();

        return $finiquitos;
    }
}
