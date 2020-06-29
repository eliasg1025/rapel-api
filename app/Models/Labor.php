<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Labor extends Model
{
    protected $connection = 'sqlsrv';

    //
    protected $table = 'dbo.Actividades';

    public static function _all()
    {
        return self::whereIn('IdEmpresa', ['9', '14'])
            ->select('IdEmpresa as empresa_id', 'IdFamilia as actividad_id', 'IdActividad as id', 'Nombre as name', 'UnidadMedida as unidad_medida')
            ->get();
    }

    public static function _get($id_empresa, $id_actividad)
    {
        return self::where([
            'IdEmpresa' => $id_empresa,
            'IdFamilia' => $id_actividad,
        ])
            ->select('IdEmpresa as empresa_id', 'IdFamilia as actividad_id', 'IdActividad as id', 'Nombre as name', 'UnidadMedida as unidad_medida')
            ->get();
    }
}
