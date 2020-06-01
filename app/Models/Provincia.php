<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $connection = 'sqlsrv2';

    protected $table = 'dbo.PROVINCIAS';

    public $incrementing = false;

    public static function _all()
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_EMP' => 'ARAP',
            'COD_TEM' => '20'
        ])->select('COD_PROVC as id', 'DESCRIPCION as name', 'COD_REG as departamento_id', 'COD_PAIS as pais_id')->get();
    }

    public static function _get($codigo_departamento)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_REG' => $codigo_departamento,
            'COD_EMP' => 'ARAP',
            'COD_TEM' => '20'
        ])->select('COD_PROVC as id', 'DESCRIPCION as name', 'COD_REG as departamento_id', 'COD_PAIS as pais_id')->get();
    }

    public static function _show($codigo)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_EMP' => 'ARAP',
            'COD_PROVC' => $codigo,
            'COD_TEM' => '20',
        ])->select('COD_PROVC as id', 'DESCRIPCION as name', 'COD_REG as departamento_id', 'COD_PAIS as pais_id')->first();
    }

    public static function _distritos($codigo)
    {
        $distritos = Distrito::_get($codigo);

        return [
            'provincia' => self::_show($codigo),
            'distritos' => $distritos
        ];
    }

    public static function _departamento($codigo)
    {
        $provincia = self::_show($codigo);
        $departamento = Departamento::_show($provincia->COD_REG);

        return [
            'provincia' => self::_show($codigo),
            'departamento' => $departamento
        ];
    }
}
