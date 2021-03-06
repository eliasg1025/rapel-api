<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoVia extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TipoVia';

    public $incrementing = false;

    public static function _all()
    {
        return self::where('idEmpresa', "9")->orWhere('idEmpresa', '14')
            ->select('IdTipoVia as id', 'Descripcion as name', 'IdEmpresa as empresa_id')->get();
    }

    public static function _get($id_empresa)
    {
        return self::where([
            'IdEmpresa' => $id_empresa,
        ])->select('IdTipoVia as id', 'Descripcion as name', 'IdEmpresa as empresa_id')->get();
    }

    public static function _show($id_empresa, $id_tipo_via)
    {
        return self::where([
            'IdEmpresa' => $id_empresa,
            'IdTipoVia' => $id_tipo_via
        ])->first();
    }
}
