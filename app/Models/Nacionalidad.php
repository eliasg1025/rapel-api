<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nacionalidad extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Nacionalidad';

    public static function _get($id_empresa)
    {
        return self::where('idEmpresa', $id_empresa)->get();
    }

    public static function _show($id_empresa, $id_nacionalidad)
    {
        return self::where([
            'idEmpresa' => $id_empresa,
            'idNacionalidad' => $id_nacionalidad
        ])->first();
    }
}
