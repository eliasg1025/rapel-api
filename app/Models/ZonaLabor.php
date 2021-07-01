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

    public static function _show($id_empresa, $id)
    {
        switch ($id) {
            case 65:
                $id = 55;
                break;
            default:
                break;
        }

        $zona = self::where([
            'IdEmpresa' => $id_empresa,
            'IdZona' => $id
        ])->select('IdZona as id', 'IdEmpresa as empresa_id', 'Nombre as name')->first();

        $name = trim(explode('(', $zona->name)[0]);

        $zona_labor = ZonaLabor::select('IdZona as id', 'IdEmpresa as empresa_id', 'Nombre as name')->whereIn('IdEmpresa', ['9', '14'])
            ->where('Nombre', 'like', '%' . $name . '%')
            ->where('Nombre', 'not like', '%OBREROS%')
            ->first();

        if ($zona_labor) {
            return $zona_labor;
        }

        return $zona;
    }
}
