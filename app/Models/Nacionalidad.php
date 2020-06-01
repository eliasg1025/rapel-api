<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nacionalidad extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Nacionalidad';

    public $incrementing = false;

    public static function _all()
    {
        return self::where('idEmpresa', "9")->orWhere('idEmpresa', '14')
            ->select('IdEmpresa as empresa_id', 'IdNacionalidad as id', 'Descripcion as name')->get();
    }

    public static function _get($id_empresa)
    {
        return self::where([
            'idEmpresa' => $id_empresa
        ])->select('IdEmpresa as empresa_id', 'IdNacionalidad as id', 'Descripcion as name')->get();
    }

    public static function _show($id_empresa, $id_nacionalidad)
    {
        return self::where([
            'idEmpresa' => $id_empresa,
            'idNacionalidad' => $id_nacionalidad
        ])->first();
    }
}
