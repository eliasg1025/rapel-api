<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoZona extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TipoZona';

    public static function _show($id_empresa, $id_tipo_zona)
    {
        return self::where([
            'idEmpresa' => $id_empresa,
            'idTipoZona' => $id_tipo_zona
        ]);
    }
}
