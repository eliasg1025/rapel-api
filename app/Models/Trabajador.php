<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trabajador extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Trabajador';

    public static function _show($dni, $activo=true, $info_jornal=false)
    {
        try {
            $t =  Trabajador::where('RutTrabajador', $dni)->whereIn('IdEmpresa', ['9', '14'])->orderBy('IdTrabajador', 'DESC')->first();

            $alertas = AlertaTrabajador::get($dni);
            $contrato_activo = Contrato::activo($dni, $activo, $info_jornal);

            return [
                'rut' => $dni,
                'trabajador' => [
                    'rut' => $dni,
                    'code' => $t->IdTrabajador,
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
                    'empresa_id' => $t->IdEmpresa,
                    'numero_cuenta' => $t->NumeroCuentaBancaria,
                    'banco_id' => $t->IdBanco
                ],
                'alertas' => $alertas,
                'contrato_activo' => $contrato_activo,
            ];
        } catch (\Exception $e) {
            return $e->getMessage() . ' -- ' . $e->getLine();
        }
    }

    public static function _info($dni, $activo=true, $info_jornal=false)
    {
        try {
            $info = self::_show($dni, $activo, $info_jornal);

            foreach ($info['contrato_activo'] as &$contrato_activo) {
                $contrato_activo['oficio'] = Oficio::_show(
                    $contrato_activo['empresa_id'],
                    $contrato_activo['oficio_id'],
                );
                $contrato_activo['zona_labor'] = ZonaLabor::_show(
                    $contrato_activo['empresa_id'],
                    $contrato_activo['zona_id']
                );
                $contrato_activo['cuartel'] = Cuartel::_show(
                    $contrato_activo['empresa_id'],
                    $contrato_activo['zona_id'],
                    $contrato_activo['cuartel_id']
                );
                $contrato_activo['afp'] = Afp::_show(
                    $contrato_activo['empresa_id'],
                    $contrato_activo['afp_id']
                );
                $contrato_activo['regimen'] = Regimen::_show(
                    $contrato_activo['regimen_id']
                );
                unset($contrato_activo['cuartel_id']);
                unset($contrato_activo['oficio_id']);
                unset($contrato_activo['zona_id']);
            }

            return $info;
        } catch (\Exception $e) {
            return $e->getMessage() . ' -- ' . $e->getLine();
        }
    }

    public static function buscar(string $busqueda)
    {
        $trabajadores = DB::table('dbo.Trabajador as t')
            ->select(
                't.IdTrabajador',
                't.IdEmpresa',
                DB::raw("(cast (t.Nombre as varchar) + cast(' ' as varchar) + cast(t.ApellidoPaterno as varchar) + cast(' ' as varchar) + cast(t.ApellidoMaterno as varchar)) as Nombres")
            );

        return DB::table('dbo.Trabajador as t')
            ->select(
                't.IdTrabajador as id',
                't.RutTrabajador as rut',
                'c.IdEmpresa as empresa_id',
                'trab.Nombres as nombre_completo',
            )
            ->join('dbo.Contratos as c', [
                'c.IdEmpresa'     => 't.IdEmpresa',
                'c.RutTrabajador' => 't.RutTrabajador'
            ])
            ->joinSub($trabajadores, 'trab', function($join) {
                $join->on('trab.IdTrabajador', '=', 't.IdTrabajador')
                ->on('trab.IdEmpresa', '=', 't.IdEmpresa');
            })
            ->whereIn('c.IdEmpresa', [9, 14])
            ->whereIn('c.IdRegimen', [1, 2])
            ->where('c.IndicadorVigencia', '1')
            ->where('c.Jornal', '0')
            ->where('trab.Nombres', 'like', '%' . $busqueda . '%')
            ->get();
    }

    public static function revision(array $trabajadores=[], $con_trabajador=true)
    {
        $registrados = [];
        $no_registrados = [];

        foreach ($trabajadores as $trabajador) {
            $rut = $trabajador['rut'];
            $t =  Trabajador::where('RutTrabajador', $rut)->whereIn('IdEmpresa', ['9', '14'])->first();

            $alertas = AlertaTrabajador::get($trabajador['rut']);
            $contrato_activo = Contrato::activo($trabajador['rut']);

            if ($t) {
                $data_trabajador = $con_trabajador ? [
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
                ] : null;
                array_push($registrados, [
                    'rut' => $rut,
                    'trabajador' => $data_trabajador,
                    'contrato' => $trabajador,
                    'alertas' => $alertas,
                    'contrato_activo' => $contrato_activo
                ]);
            } else {
                array_push($no_registrados, [
                    'rut' => $rut,
                    'contrato' => $trabajador,
                    'trabajador' => null,
                ]);
            }
        }

        return [
            'registrados' => $registrados,
            'no_registrados' => $no_registrados
        ];
    }
}
