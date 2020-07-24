<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MotivoPermiso extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.MotivoPermiso';

    public $incrementing = false;

    public static function _get($id_empresa)
    {
        return self::where('IdEmpresa', $id_empresa)
            ->select('IdEmpresa as empresa_id', 'IdMotivoPermiso as  id', 'Descripcion as name')
            ->get();
    }
}
