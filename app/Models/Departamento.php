<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $connection = 'sqlsrv2';

    protected $table = 'dbo.REGION';

    public static function _get()
    {
        return self::where('COD_PAIS', 'PE')->get();
    }

    public static function _show($codigo)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_REG' => $codigo
        ])->first();
    }

    public static function _provincias($codigo)
    {
        $provincias = Provincia::where([
            'COD_PAIS' => 'PE',
            'COD_REG' => $codigo
        ])->get();

        return [
            'departamento' => self::_show($codigo),
            'provincias' => $provincias
        ];
    }
}
