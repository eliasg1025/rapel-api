<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oficio extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Oficio';

    public $incrementing = false;

    public static function _all()
    {
        return self::whereIn('IdEmpresa', ['9', '14'])
            ->select('IdEmpresa as empresa_id', 'IdOficio as  id', 'Cod_Equ as cod_equ', 'Descripcion as name')
            ->get();
    }

    public static function _get($id_empresa)
    {
        return self::where([
            'IdEmpresa' => $id_empresa
        ])->select('IdEmpresa as empresa_id', 'IdOficio as  id', 'Cod_Equ as cod_equ', 'Descripcion as name')->get();
    }

    public static function _show($id_empresa, $id)
    {
        return self::where([
            'IdEmpresa' => $id_empresa,
            'IdOficio'  => $id,
        ])->select('IdEmpresa as empresa_id', 'IdOficio as  id', 'Cod_Equ as cod_equ', 'Descripcion as name')->first();
    }
}
