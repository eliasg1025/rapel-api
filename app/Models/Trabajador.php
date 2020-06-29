<?php

namespace App\Models;

use Carbon\Carbon;
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

        $contratos = Contrato::where($conditions)
                        ->orderBy('FechaInicio', 'desc')
                        ->select('IdContrato as code', 'FechaInicio as fecha_inicio', 'FechaTermino as fecha_termino', 'FechaTerminoC as fecha_termino_c', 'SueldoBase as sueldo_base', 'Cussp as cussp', 'IdTrabajador as trabajador_code', 'IdEmpresa as empresa_code', 'IdZona as zona_labor_code')
                        ->get();

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

    public static function revision(array $trabajadores=[])
    {
        $registrados = [];
        $no_registrados = [];

        foreach ($trabajadores as $trabajador) {
            $rut = $trabajador['rut'];
            $t =  Trabajador::where([
                'RutTrabajador' => $rut,
                'IdEmpresa' => ['9', '14']
            ])->first();

            $alertas = AlertaTrabajador::get($trabajador['rut']);
            $contrato_activo = Contrato::activo($trabajador['rut']);

            if ($t) {
                array_push($registrados, [
                    'rut' => $rut,
                    'trabajador' => [
                        'nombre' => $t->Nombre,
                        'apellido_paterno' => $t->ApellidoPaterno,
                        'apellido_materno' => $t->ApellidoMaterno,
                        'fecha_nacimiento' => Carbon::parse($t->FechaNacimiento)->format('Y-m-d'),
                        'sexo' => $t->Sexo,
                        'email' => $t->Mail,
                        'tipo_zona_id' => $t->IdTipoZona,
                        'nombre_zona' => $t->NombreZona,
                        'tipo_via_id' => $t->IdTipoVia,
                        'nombre_via' => $t->NombreVia,
                        'direccion' => $t->Direccion,
                        'distrito_id' => $t->COD_COM,
                        'estado_civil_id' => $t->EstadoCivil,
                        'nacionalidad_id' => $t->IdNacionalidad,
                    ],
                    'contrato' => $trabajador,
                    'alertas' => $alertas,
                    'contrato_activo' => $contrato_activo
                ]);
            } else {
                array_push($no_registrados, $trabajador);
            }
        }

        return [
            'registrados' => $registrados,
            'no_registrados' => $no_registrados
        ];
    }
}
