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
}
