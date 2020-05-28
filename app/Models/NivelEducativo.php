<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NivelEducativo extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.NivelEducativo';

    public static function _get($id_empresa)
    {
        return self::where('idEmpresa', $id_empresa)->get();
    }

    public static function _show($id_empresa, $id_nivel_educativo)
    {
        return self::where([
            'idEmpresa' => $id_empresa,
            'idNivel' => $id_nivel_educativo
        ])->first();
    }
}
