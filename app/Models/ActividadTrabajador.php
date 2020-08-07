<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ActividadTrabajador extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.ActividadTrabajador';

    public $incrementing = false;

    public static function getUltimoDiaLaborado($rut)
    {
        return DB::table('dbo.ActividadTrabajador')
            ->where([
                'RutTrabajador' => $rut,
            ])
            ->select('IdZona as zona_labor_id', 'IdCuartel as cuartel_id')
            ->orderBy('FechaActividad', 'DESC')
            ->get()->toArray();
    }
}
