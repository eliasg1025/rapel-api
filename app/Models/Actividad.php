<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.FamiliaActividades';

    public $incrementing = false;

    public static function _all()
    {
        return self::where('IdEmpresa', '9')->orWhere('IdEmpresa', '14')
            ->select('IdEmpresa as empresa_id', 'IdFamilia as  id', 'COD_CUENTA as cod_cuenta', 'Nombre as name')
            ->get();
    }

    public static function _get($id_empresa)
    {
        return self::where('IdEmpresa', $id_empresa)
            ->select('IdEmpresa as empresa_id', 'IdFamilia as  id', 'COD_CUENTA as cod_cuenta', 'Nombre as name')
            ->get();
    }
}
