<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaLabor extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Zona';

    public $incrementing = false;

    public static function _all()
    {
        return self::where('IdEmpresa', "9")->orWhere('IdEmpresa', "14")
            ->select('IdZona as id', 'IdEmpresa as empresa_id', 'Nombre as name')->get();
    }

    public static function _get($id_empresa)
    {
        return self::where([
            'IdEmpresa' => $id_empresa
        ])->select('IdZona as id', 'IdEmpresa as empresa_id', 'Nombre as name')->get();
    }
}
