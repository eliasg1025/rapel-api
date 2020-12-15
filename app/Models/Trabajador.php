<?php

namespace App\Models;

use Carbon\Carbon;
use Error;
use Exception;
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

            if (!$t) {
               throw new Exception('No se encontro trabajador');
            }

            $alertas = AlertaTrabajador::get($dni);
            $contrato_activo = Contrato::activo($dni, $activo, $info_jornal);

            return [
                'rut' => $dni,
                'trabajador' => [
                    'rut' => $dni,
                    'code' => $contrato_activo[0]->trabajador_code,
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
                    'empresa_id' => $contrato_activo[0]->empresa_id,
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

    public static function infoSctr($dni)
    {
        try {
            $trabajador = DB::table('dbo.Trabajador as t')
                ->select(
                    't.RutTrabajador as rut',
                    't.Nombre as nombre',
                    't.ApellidoPaterno as apellido_paterno',
                    't.ApellidoMaterno as apellido_materno',
                    DB::raw('CONVERT(varchar, t.FechaNacimiento, 23) fecha_nacimiento'),
                    DB::raw('DATEDIFF(YEAR, t.FechaNacimiento, GETDATE()) as edad'),
                    't.Sexo as sexo',
                    't.Direccion as direccion',
                    't.COD_COM as distrito_id',
                    't.EstadoCivil as estado_civil',
                    't.Telefono as telefono',
                    'n.Descripcion as nacionalidad'
                )
                ->join('dbo.Nacionalidad as n', [
                    'n.IdEmpresa' => 't.IdEmpresa',
                    'n.IdNacionalidad' => 't.IdNacionalidad'
                ])
                ->where('RutTrabajador', $dni)
                ->whereIn('t.IdEmpresa', ['9', '14'])
                ->first();

            $contrato_activo = self::_info($dni)['contrato_activo'];

            return [
                'rut' => $dni,
                'trabajador' => $trabajador,
                'contrato_activo' => $contrato_activo
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public static function infoPeriodos($dni)
    {
        try {
            $alertas = AlertaTrabajador::get($dni);
            $contratos = Contrato::byPeriodo($dni);

            if ( sizeof($contratos) !== 0 ) {
                //throw new Error('Esta persona no tiene contratos');
                $ultimoContrato = $contratos[0];
                $empresasId = [$ultimoContrato->empresa_id];
            } else {
                $empresasId = [9, 14];
            }


            $trabajador = DB::table('dbo.Trabajador as t')
                ->select(
                    't.RutTrabajador as rut',
                    't.Nombre as nombre',
                    't.ApellidoPaterno as apellido_paterno',
                    't.ApellidoMaterno as apellido_materno',
                    DB::raw('CONVERT(varchar, t.FechaNacimiento, 23) fecha_nacimiento'),
                    DB::raw('DATEDIFF(YEAR, t.FechaNacimiento, GETDATE()) as edad'),
                    't.Sexo as sexo',
                    't.Direccion as direccion',
                    't.COD_COM as distrito_id',
                    't.EstadoCivil as estado_civil',
                    't.Telefono as telefono',
                    'n.Descripcion as nacionalidad',
                    'z.Nombre as zona_labor'
                )
                ->leftJoin('dbo.Nacionalidad as n', [
                    'n.IdEmpresa' => 't.IdEmpresa',
                    'n.IdNacionalidad' => 't.IdNacionalidad'
                ])
                ->leftJoin('dbo.Zona as z', [
                    't.IdEmpresa' => 'z.IdEmpresa',
                    't.IdZonaLabores' => 'z.IdZona'
                ])
                ->where('RutTrabajador', $dni)
                ->whereIn('t.IdEmpresa', $empresasId)
                ->first();

            if ( sizeof($contratos) !== 0 ) {
                $ultimoContrato->zona_labor = !is_null($trabajador->zona_labor) ? $trabajador->zona_labor : $ultimoContrato->zona_labor;
            }

            return [
                'rut' => $dni,
                'trabajador' => $trabajador,
                'alertas' => $alertas,
                'periodos' => $contratos,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage() . ' -- ' . $e->getLine()
            ];
        }
    }

    public static function getTrabajadoresSctr($empresa_id, $oficios_indexes, $cuarteles_indexes, $actual, $fechas)
    {
        $condicion = "(cast(c.IdZona as varchar) + '@' + cast(c.IdCuartel as varchar)) in (";
        for ($i=1; $i <= sizeof($cuarteles_indexes); $i++) {
            $condicion .= '?,';
        }
        $condicion = substr($condicion, 0, -1);
        $condicion .= ')';

        $trabajadores = DB::table('dbo.Trabajador as t')
            ->select(
                't.RutTrabajador as key',
                't.ApellidoPaterno as apellido_paterno',
                't.ApellidoMaterno as apellido_materno',
                't.Nombre as nombres',
                't.Sexo as sexo',
                DB::raw('CONVERT(varchar, t.FechaNacimiento, 103) fecha_nacimiento'),
                't.IdTipoDctoIden as tipo_documento',
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS rut
                "),
                'o.Descripcion as cargo',
                'c.IdRegimen as regimen_id',
                DB::raw('
                    CASE
                        WHEN c.IdRegimen = 2
                            THEN CAST(ROUND(c.SueldoBase, 2, 0) as decimal(18, 2))
                        WHEN c.IdRegimen = 3
                            THEN CAST(ROUND(c.SueldoBase * 1.2638 * 30, 2, 0) as decimal(18, 2))
                        ELSE
                            CAST(ROUND(c.SueldoBase * 1.2638, 2, 0) as decimal(18, 2))
                    END AS sueldo
                '),
                DB::raw('CONVERT(varchar, c.FechaInicioPeriodo, 103) fecha_ingreso')
            )
            ->join('dbo.Contratos as c', [
                'c.IdEmpresa' => 't.IdEmpresa',
                'c.RutTrabajador' => 't.RutTrabajador'
            ])
            ->join('dbo.Oficio as o', [
                'o.IdEmpresa' => 'c.IdEmpresa',
                'o.IdOficio' => 'c.IdOficio'
            ])
            ->where('c.IndicadorVigencia', true)
            ->whereNull('c.FechaTermino')
            ->when($actual, function($query) {
                $query->whereDate('c.FechaInicioPeriodo', '<=', Carbon::now()->lastOfMonth());
            })
            ->when(!$actual, function($query) use ($fechas) {
                $query->whereDate('c.FechaInicioPeriodo', '>=', Carbon::parse($fechas['desde']))
                    ->whereDate('c.FechaInicioPeriodo', '<=', Carbon::parse($fechas['hasta']));
            })
            ->where(function($query) use ($empresa_id, $oficios_indexes, $condicion, $cuarteles_indexes) {
                $query
                    ->where(function($query) use ($empresa_id, $oficios_indexes) {
                        $query->where('c.IdEmpresa', $empresa_id)
                            ->whereIn('c.IdOficio', $oficios_indexes);
                    })
                    ->orWhere(function($query) use ($empresa_id, $condicion, $cuarteles_indexes) {
                        $query->where('c.IdEmpresa', $empresa_id)
                            ->whereRaw($condicion, $cuarteles_indexes);
                    });
            })
            ->orderBy('t.ApellidoPaterno', 'ASC')
            ->get();

        return $trabajadores;
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

    public static function buscarTodos(string $busqueda)
    {
        $trabajadores = DB::table('dbo.Trabajador as t')
            ->select(
                't.IdTrabajador',
                't.IdEmpresa',
                DB::raw("(cast(t.ApellidoPaterno as varchar) + cast(' ' as varchar) + cast(t.ApellidoMaterno as varchar) + cast(' ' as varchar) + cast (t.Nombre as varchar)) as Nombres")
            );

        return DB::table('dbo.Trabajador as t')
            ->select(
                't.RutTrabajador as rut',
                'trab.Nombres as nombre_completo',
            )
            ->joinSub($trabajadores, 'trab', function($join) {
                $join->on('trab.IdTrabajador', '=', 't.IdTrabajador')
                ->on('trab.IdEmpresa', '=', 't.IdEmpresa');
            })
            ->whereIn('t.IdEmpresa', [9, 14])
            ->where('trab.Nombres', 'like', '%' . $busqueda . '%')
            ->distinct()
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

    public static function getActivos( int $empresaId = 0 )
    {
        return DB::table('dbo.Contratos as c')
            ->select(
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS id
                "),
                't.Nombre as nombre',
                't.ApellidoPaterno as apellido_paterno',
                't.ApellidoMaterno as apellido_materno',
                DB::raw('
                    CASE
                        WHEN c.IdRegimen = 2
                            THEN CAST(ROUND(c.SueldoBase, 2, 0) as decimal(18, 2))
                        WHEN c.IdRegimen = 3
                            THEN CAST(ROUND(c.SueldoBase * 1.2638 * 30, 2, 0) as decimal(18, 2))
                        ELSE
                            CAST(ROUND(c.SueldoBase * 1.2638, 2, 0) as decimal(18, 2))
                    END AS sueldo_bruto
                '),
                'o.Descripcion as oficio',
                'c.Jornal as jornal',
                'c.IdRegimen as regimen_id',
                'c.IdEmpresa as empresa_id'
            )
            ->join('dbo.Trabajador as t', [
                't.IdEmpresa' => 'c.IdEmpresa',
                't.RutTrabajador' => 'c.RutTrabajador'
            ])
            ->join('dbo.Banco as b', [
                'b.IdEmpresa' => 't.IdEmpresa',
                'b.IdBanco' => 't.IdBanco'
            ])
            ->join('dbo.Oficio as o', [
                'o.IdOficio' => 'c.IdOficio',
                'o.IdEmpresa' => 'c.IdEmpresa'
            ])
            ->when($empresaId === 0, function($query) {
                $query->whereIn('c.IdEmpresa', [9, 14]);
            })
            ->when($empresaId !== 0, function($query) use ($empresaId) {
                $query->where('c.IdEmpresa', $empresaId);
            })
            ->where('c.IndicadorVigencia', true)
            ->get();
    }

    public static function getDetallePlanilla( int $empresaId = 9, $periodo, $zonaLaborId = 0 )
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        $detalles = DB::table('Liquidacion as l')
            ->select(
                'l.idLiquidacion as liquidacion_id',
                DB::raw("(CAST(co.IdConcepto AS NVARCHAR(8)) + ' ' + co.Descripcion) as concepto"),
                DB::raw("
                    CAST((
                        CASE
                            WHEN Afp.idsistemaPublico=1 AND (Afp.IdSSS=1 OR Afp.IdEmpart=1) THEN
                            (
                                CASE
                                    WHEN dl.IdConcepto = 504
                                    THEN (dl.Monto + l.MontoAfp)
                                    ELSE dl.Monto
                                END
                            )
                            ELSE
                            (
                                CASE
                                    WHEN dl.IdConcepto = 504
                                    THEN dl.Monto
                                    ELSE dl.Monto
                                END
                            )
                            END
                    ) AS DECIMAL(18, 2)) as monto_haber_descuento
                "),
                DB::raw("(CASE WHEN Indicadordebe = 1 THEN 'HABERES' ELSE 'DESCUENTOS' END) as tipo_haber_descuento")
            )
            ->join('DetalleLiquidacion as dl', 'l.idLiquidacion', '=', 'dl.idLiquidacion')
            ->join('ConceptosHaberDescuento as co', [
                'dl.IdConcepto' => 'co.IdConcepto',
                'dl.IdEmpresa'  => 'co.IdEmpresa'
            ])
            ->join('Afp', [
                'Afp.IdEmpresa' => 'l.IdEmpresa',
                'Afp.IdAfp' => 'l.IdAfp'
            ])
            ->where('l.idEmpresa', $empresaId)
            ->where([
                'l.Mes' => $mes,
                'l.Ano' => $anio
            ])
            ->whereRaw("
                (
                    co.Total = 0 or dl.idconcepto in (251,287,505,504,101,560,581,141,288,285,286,248,503)
                )
            ")
            ->when($zonaLaborId != 0, function ($query) use ($empresaId, $zonaLaborId) {
                $query->where([
                    'l.idEmpresa' => $empresaId,
                    'l.idZona'    => $zonaLaborId,
                ]);
            })
            ->get();

        DB::select("
            SELECT
                Liquidacion.IdLiquidacion as liquidacion_id,
                [concepto] = CAST(co.IdConcepto AS NVARCHAR(8)) + ' ' + co.Descripcion,
                [monto_haber_descuento] =
                    CAST((
                        CASE
                            WHEN Afp.idsistemaPublico=1 AND (Afp.IdSSS=1 OR Afp.IdEmpart=1) THEN
                            (
                                CASE
                                    WHEN DetalleLiquidacion.IdConcepto = 504
                                    THEN (DetalleLiquidacion.Monto + Liquidacion.MontoAfp)
                                    ELSE DetalleLiquidacion.Monto
                                END
                            )
                            ELSE
                            (
                                CASE
                                    WHEN DetalleLiquidacion.IdConcepto = 504
                                    THEN DetalleLiquidacion.Monto
                                    ELSE DetalleLiquidacion.Monto
                                END
                            )
                            END
                    ) AS DECIMAL(18, 2)),
                [tipo_haber_descuento] = (CASE WHEN Indicadordebe = 1 THEN 'HABERES' ELSE 'DESCUENTOS' END)
            FROM Liquidacion WITH(NOLOCK)
            INNER JOIN DetalleLiquidacion WITH(NOLOCK) ON Liquidacion.IdLiquidacion = DetalleLiquidacion.IdLiquidacion
            INNER JOIN ConceptosHaberDescuento co WITH(NOLOCK) ON DetalleLiquidacion.IdConcepto = co.IdConcepto AND DetalleLiquidacion.IdEmpresa = co.IdEmpresa
            INNER JOIN Afp WITH(NOLOCK) ON Afp.IdEmpresa = liquidacion.IdEmpresa AND Afp.IdAfp = liquidacion.IdAfp
            WHERE Liquidacion.IdEmpresa in ($empresaId)
            and Liquidacion.Mes = $mes and Liquidacion.Ano = $anio and Liquidacion.IdZona = $zonaLaborId
            AND (
                co.Total = 0 or DetalleLiquidacion.idconcepto in (251,287,505,504,101,560,581,141,288,285,286,248,503)
            )
        ");

        return [
            'count'         => sizeof($detalles),
            'detalles'      => $detalles
        ];
    }

    public static function detallePlanillaGenerator( int $empresaId = 9, $periodo, $zonaLaborId = 0 )
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        foreach (
            DB::table('Liquidacion as l')
            ->select(
                'dl.IdDetalle as id',
                'l.idLiquidacion as liquidacion_id',
                DB::raw("(CAST(co.IdConcepto AS NVARCHAR(8)) + ' ' + co.Descripcion) as concepto"),
                DB::raw("
                    CAST((
                        CASE
                            WHEN Afp.idsistemaPublico=1 AND (Afp.IdSSS=1 OR Afp.IdEmpart=1) THEN
                            (
                                CASE
                                    WHEN dl.IdConcepto = 504
                                    THEN (dl.Monto + l.MontoAfp)
                                    ELSE dl.Monto
                                END
                            )
                            ELSE
                            (
                                CASE
                                    WHEN dl.IdConcepto = 504
                                    THEN dl.Monto
                                    ELSE dl.Monto
                                END
                            )
                            END
                    ) AS DECIMAL(18, 2)) as monto_haber_descuento
                "),
                DB::raw("(CASE WHEN Indicadordebe = 1 THEN 'HABERES' ELSE 'DESCUENTOS' END) as tipo_haber_descuento")
            )
            ->join('DetalleLiquidacion as dl', 'l.idLiquidacion', '=', 'dl.idLiquidacion')
            ->join('ConceptosHaberDescuento as co', [
                'dl.IdConcepto' => 'co.IdConcepto',
                'dl.IdEmpresa'  => 'co.IdEmpresa'
            ])
            ->join('Afp', [
                'Afp.IdEmpresa' => 'l.IdEmpresa',
                'Afp.IdAfp' => 'l.IdAfp'
            ])
            ->where('l.idEmpresa', $empresaId)
            ->where([
                'l.Mes' => $mes,
                'l.Ano' => $anio
            ])
            ->whereRaw("
                (
                    co.Total = 0 or dl.idconcepto in (251,287,505,504,101,560,581,141,288,285,286,248,503)
                )
            ")
            ->when($zonaLaborId != 0, function ($query) use ($empresaId, $zonaLaborId) {
                $query->where([
                    'l.idEmpresa' => $empresaId,
                    'l.idZona'    => $zonaLaborId,
                ]);
            })->cursor() as $detalle
        ) {
            yield $detalle;
        }
    }
}
