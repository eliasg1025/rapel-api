<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.RUTAS';

    public static function _get($id_empresa, $codigo_troncal)
    {
        return self::where([
            'IDEMPRESA' => $id_empresa,
            'COD_TRONCAL' => $codigo_troncal
        ])->first();
    }

    public static function _show($id_empresa, $codigo_troncal, $codigo_ruta)
    {
        return self::where([
            'IDEMPRESA' => $id_empresa,
            'COD_TRONCAL' => $codigo_troncal,
            'COD_RUTA' => $codigo_ruta
        ])->first();
    }

    public static function _troncal($id_empresa, $codigo_troncal, $codigo_ruta)
    {
        $troncal = Troncal::_show($id_empresa, $codigo_troncal);

        return [
            'ruta' => self::_show($id_empresa, $codigo_troncal, $codigo_ruta),
            'troncal' => $troncal
        ];
    }
}
