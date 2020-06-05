<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Trabajador';

    public static function _show($id_empresa, $dni)
    {
        $conditions = [
            'idEmpresa' => $id_empresa,
            'RutTrabajador' => $dni
        ];

        $trabajador = self::where($conditions)->first();

        $contratos = Contrato::where($conditions)->orderBy('FechaInicio', 'desc')->get();

        $trabajador->contratos = $contratos;

        return $trabajador;
    }

    public static function _info($id_empresa, $dni)
    {
        $trabajador = self::_show($id_empresa, $dni);
        $nacionalidad = Nacionalidad::_show($id_empresa, $trabajador->IdNacionalidad);
        $localidad = Distrito::_provincia($trabajador->COD_COM);
        $nivel_educativo = NivelEducativo::_show($id_empresa, $trabajador->IdNivel);
        $tipo_via = TipoVia::_show($id_empresa, $trabajador->IdTipoVia);
        $tipo_zona = TipoZona::_show($id_empresa, $trabajador->IdTipoZona);
        $ruta = Ruta::_troncal($id_empresa, $trabajador->COD_TRONCAL, $trabajador->COD_RUTA);

        return [
            'trabajador' => $trabajador,
            'nacionalidad' => $nacionalidad,
            'localidad' => $localidad,
            'nivel_educativo' => $nivel_educativo,
            'tipo_via' => $tipo_via,
            'tipo_zona' => $tipo_zona,
            'ruta' => $ruta,
        ];
    }
}
