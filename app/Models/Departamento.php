<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $connection = 'sqlsrv2';

    protected $table = 'dbo.REGION';

    public $incrementing = false;

    public static function _get()
    {
        return self::where('COD_PAIS', 'PE')->select('COD_REG as id', 'NOMBRE as name', 'COD_PAIS as pais_id')->get();
    }

    public static function _show($codigo)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_REG' => $codigo
        ])->select('COD_REG as id', 'NOMBRE as name', 'COD_PAIS as pais_id')->first();
    }

    public static function _provincias($codigo)
    {
        $provincias = Provincia::_get($codigo);

        return [
            'departamento' => self::_show($codigo),
            'provincias' => $provincias
        ];
    }
}
