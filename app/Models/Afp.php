<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Afp extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Afp';

    public $incrementing = false;

    public static function _all()
    {
        return self::where('IdEmpresa', '9')->orWhere('IdEmpresa', '14')
            ->select('IdEmpresa as empresa_id', 'IdAfp as  id', 'Nombre as name', 'idSistemaPublico as publico')
            ->get();
    }

    public static function _get($id_empresa)
    {
        return self::where('IdEmpresa', $id_empresa)
            ->select('IdEmpresa as empresa_id', 'IdAfp as  id', 'Nombre as name', 'idSistemaPublico as publico')
            ->where('Nombre', '<>', 'SIN REGIMEN PENSIONARIO')
            ->get();
    }
}
