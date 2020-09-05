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
        $finiquitos = DB::table('dbo.Liquidacion as l')
            ->select(
                'l.IdLiquidacion',
                'l.IdFiniquito',
                'l.IdEmpresa',
                'l.RutTrabajador',
                't.Nombre',
                't.ApellidoPaterno',
                't.ApellidoMaterno',
                'l.Mes',
                'l.Ano',
                'l.FechaEmision',
                DB::raw("CAST(ROUND(l.MontoAPagar, 2, 0) as decimal(18, 2)) MontoAPagar"),
                'b.Nombre as Banco',
                't.NumeroCuentaBancaria'
            )
            ->join('dbo.Trabajador as t', [
                't.IdEmpresa' => 'l.IdEmpresa',
                'l.RutTrabajador' => 't.RutTrabajador'
            ])
            ->join('dbo.Banco as b', [
                't.IdBanco' => 'b.IdBanco',
                't.IdEmpresa' => 'b.IdEmpresa'
            ])
            ->where('l.IdFiniquito', '<>', '0')
            ->whereDate('l.FechaEmision', '>=', $desde)->whereDate('l.FechaEmision', '<=', $hasta)
            ->when($empresa_id === 0, function($query) {
                $query->whereIn('l.IdEmpresa', [9, 14]);
            })
            ->when($empresa_id !== 0, function($query) use ($empresa_id) {
                $query->where('l.IdEmpresa', $empresa_id);
            })
            ->get();

        return $finiquitos;
    }
}
