<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuartel extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Cuartel';

    public $incrementing = false;

    public static function _all()
    {
        return self::whereIn('IdEmpresa', ['9', '14'])
            ->select('IdCuartel as id', 'IdZona as zona_labor_id', 'Nombre as name', 'COD_SUBCENTRO as cod_subcentro', 'NOM_SUBCENTRO as nom_subcentro')
            ->get();
    }

    public static function _get($id_empresa, $id_zona_labor)
    {
        return self::where([
            'IdEmpresa' => $id_empresa,
            'IdZona'    => $id_zona_labor
        ])
            ->select('IdCuartel as id', 'IdZona as zona_labor_id', 'Nombre as name', 'COD_SUBCENTRO as cod_subcentro', 'NOM_SUBCENTRO as nom_subcentro')
            ->get();
    }
}
