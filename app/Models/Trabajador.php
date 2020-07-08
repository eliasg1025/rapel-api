<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Trabajador extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Trabajador';

    public static function _show($dni)
    {
        $t =  Trabajador::where('RutTrabajador', $dni)->whereIn('IdEmpresa', ['9', '14'])->first();

        $alertas = AlertaTrabajador::get($dni);
        $contrato_activo = Contrato::activo($dni);

        return [
            'rut' => $dni,
            'trabajador' => [
                'rut' => $dni,
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
                'empresa_id' => $t->IdEmpresa
            ],
            'alertas' => $alertas,
            'contrato_activo' => $contrato_activo
        ];
    }

    public static function _info($id_empresa, $dni)
    {
        $trabajador = self::_show($dni);
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
            $t =  Trabajador::where('RutTrabajador', $rut)->whereIn('IdEmpresa', ['9', '14'])->first();

            $alertas = AlertaTrabajador::get($trabajador['rut']);
            $contrato_activo = Contrato::activo($trabajador['rut']);

            if ($t) {
                array_push($registrados, [
                    'rut' => $rut,
                    'trabajador' => [
                        'rut' => $rut,
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
                        'empresa_id' => $t->IdEmpresa
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
