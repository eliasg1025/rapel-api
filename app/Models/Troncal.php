<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Troncal extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TRONCAL';

    public static function _get($id_empresa)
    {
        return self::where('IDEMPRESA', $id_empresa)->get();
    }

    public static function _show($id_empresa, $codigo)
    {
        return self::where([
            'IDEMPRESA' => $id_empresa,
            'COD_TRONCAL' => $codigo
        ])->first();
    }

    public static function _rutas($id_empresa, $codigo)
    {
        $rutas = Ruta::_get($id_empresa, $codigo);

        return [
            'troncal' => Troncal::_show($id_empresa, $codigo),
            'rutas' => $rutas
        ];
    }
}
