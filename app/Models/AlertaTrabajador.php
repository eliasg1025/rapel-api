<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertaTrabajador extends Model
{
    protected $connection = 'sqlsrv';

    //
    protected $table = 'dbo.AlertaTrabajadores';

    public static function get($rut)
    {
        return self::where([
            'RutTrabajador' => $rut
        ])->select('IdEmpresa as empresa_id', 'RutTrabajador as rut', 'Digito as digito', 'Observacion as observacion', 'Fecha as fecha', 'IdZona as zona_id')
        ->get();
    }
}
