<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoVia extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TipoVia';

    public static function _show($id_empresa, $id_tipo_via)
    {
        return self::where([
            'idEmpresa' => $id_empresa,
            'idTipoVia' => $id_tipo_via
        ])->first();
    }
}
