<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Contratos';

    public $incrementing = false;

    public static function byTrabajador($empresa_id, $dni)
    {
        return self::where([
            'IdEmpresa' => $empresa_id,
            'RutTrabajador' => $dni
        ])->get();
    }

    public static function activo($rut)
    {
        return self::where([
            'RutTrabajador' => $rut,
            'IndicadorVigencia' => '1'
        ])
        ->select(
            'IdContrato as contrato_id',
            'IdEmpresa as empresa_id',
            'FechaInicioPeriodo as fecha_inicio',
            'IdZona as zona_id',
            'FechaTerminoC as fecha_termino_c',
            'IdAfp as afp_id'
        )
        ->get();
    }
}
