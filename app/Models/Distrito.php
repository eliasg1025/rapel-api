<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    protected $connection = 'sqlsrv2';

    protected $table = 'dbo.COMUNAS';

    public static function _show($codigo)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_COM' => $codigo
        ])->first();
    }
}
