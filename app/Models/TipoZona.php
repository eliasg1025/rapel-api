<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoZona extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TipoZona';

    public $incrementing = false;

    public static function _all()
    {
        return self::where('idEmpresa', "9")->orWhere('idEmpresa', '14')
            ->select('IdTipoZona as id', 'Descripcion as name', 'IdEmpresa as empresa_id')->get();
    }

    public static function _get($id_empresa)
    {
        return self::where([
            'IdEmpresa' => $id_empresa,
        ])->select('IdTipoZona as id', 'Descripcion as name', 'IdEmpresa as empresa_id')->get();
    }

    public static function _show($id_empresa, $id_tipo_zona)
    {
        return self::where([
            'idEmpresa' => $id_empresa,
            'idTipoZona' => $id_tipo_zona
        ])->first();
    }
}
