<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regimen extends Model
{
    protected $connection = 'sqlsrv';

    //
    protected $table = 'dbo.TipoRegimen';

    public static function _all()
    {
        return self::select('IdTipo as id', 'Descripcion as name')->get();
    }
}
