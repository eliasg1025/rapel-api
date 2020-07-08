<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Troncal extends Model
{
    protected $connection = 'sqlsrv';

    public $incrementing = false;

    protected $table = 'dbo.TRONCAL';

    public static function _get($id_empresa)
    {
        return self::where('IDEMPRESA', $id_empresa)->select('IDEMPRESA as empresa_id', 'COD_TRONCAL as id', 'DESCRIPCION as name')->get();
    }

    public static function _show($id_empresa, $codigo)
    {
        return self::where([
            'IDEMPRESA' => $id_empresa,
            'COD_TRONCAL' => $codigo
        ])
        ->select('IDEMPRESA as empresa_id', 'COD_TRONCAL as troncal_id', 'DESCRIPCION as name')
        ->first();
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
