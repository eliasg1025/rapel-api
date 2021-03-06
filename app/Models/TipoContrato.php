<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoContrato extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TipoContrato';

    public static function _all()
    {
        return self::whereIn('IdEmpresa', ['9', '14'])
            ->select('IdEmpresa as empresa_id', 'IdTipoContrato as id', 'Cod_Equ as cod_equ', 'Descripcion as name')
            ->get();
    }

    public static function _get($id_empresa)
    {
        return self::where('IdEmpresa', $id_empresa)
            ->select('IdEmpresa as empresa_id', 'IdTipoContrato as id', 'Cod_Equ as cod_equ', 'Descripcion as name')
            ->get();
    }
}
