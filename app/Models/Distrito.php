<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    protected $connection = 'sqlsrv2';

    protected $table = 'dbo.COMUNAS';

    public $incrementing = false;

    public static function _all()
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_EMP' => 'ARAP',
            'COD_TEM' => '20'
        ])->select('COD_COM as id', 'DESCRIPCION as name', 'COD_PROVC as provincia_id', 'COD_REG as departamento_id', 'COD_PAIS as pais_id')->get();
    }

    public static function _get($codigo_provincia)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_EMP' => 'ARAP',
            'COD_PROVC' => $codigo_provincia,
            'COD_TEM' => '20'
        ])->select('COD_COM as id', 'DESCRIPCION as name', 'COD_PROVC as provincia_id', 'COD_REG as departamento_id', 'COD_PAIS as pais_id')->get();
    }

    public static function _show($codigo)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_COM' => $codigo
        ])->first();
    }

    public static function _provincia($codigo)
    {
        $distrito = self::_show($codigo);
        $provincia = Provincia::_show($distrito->COD_PROVC);
        $departamento = Departamento::_show($provincia->COD_REG);

        return [
            'distrito' => $distrito,
            'provincia' => $provincia,
            'departamento' => $departamento
        ];
    }
}
