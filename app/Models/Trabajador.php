<?php

namespace App\Models;

use Carbon\Carbon;
use Error;
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

            if ( sizeof($contratos) === 0 ) {
                throw new Error('Este persona no tiene contratos');
            }

            $ultimoContrato = $contratos[0];

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
                ->join('dbo.Nacionalidad as n', [
                    'n.IdEmpresa' => 't.IdEmpresa',
                    'n.IdNacionalidad' => 't.IdNacionalidad'
                ])
                ->join('dbo.Zona as z', [
                    't.IdEmpresa' => 'z.IdEmpresa',
                    't.IdZonaLabores' => 'z.IdZona'
                ])
                ->where('RutTrabajador', $dni)
                ->where('t.IdEmpresa', $ultimoContrato->empresa_id)
                ->first();

            $ultimoContrato->zona_labor = $trabajador->zona_labor;

            return [
                'rut' => $dni,
                'trabajador' => $trabajador,
                'alertas' => $alertas,
                'periodos' => $contratos,
                'value' => $ultimoContrato,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
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
                't.IdEmpresa',
                't.RutTrabajador',
                't.Nombre',
                't.ApellidoPaterno',
                't.ApellidoMaterno',
                't.NumeroCuentaBancaria',
                'b.Nombre as Banco',
                'o.Descripcion as Oficio',
                'c.Jornal'
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

    public static function getPlanilla( int $empresaId = 9, $desde, $hasta )
    {
        // return DB::table('dbo.Liquidaciones', )

        return DB::select(
            "
            SELECT
                Liquidacion.IdLiquidacion,
                [Año] = Liquidacion.Ano,
                Mes = DATENAME(month, '01/'+LTRIM(STR(Liquidacion.Mes))+'/'+LTRIM(STR(Liquidacion.Ano))),
                [Periodo] = CAST(Liquidacion.Ano AS NVARCHAR(8)) + ' ' + DATENAME(MONTH, CAST('01/' + STR(Liquidacion.Mes) + '/00' AS SmallDatetime)),
                [Semana] = DATEPART(ww, CAST('01/' + STR(Liquidacion.Mes) + '/' + STR(Liquidacion.Ano) AS SmallDatetime)),
                T.IdTrabajador ,
                [Apellido Paterno] = APELLIDOPATERNO, [Apellido Materno]= APELLIDOMATERNO , [Nombres] = T.NOMBRE,
                [Agrupacion Trabajador] = Ag.Descripcion,
                [Institucion de Salud] = Isapre.Nombre,
                [Institucion de Prevision] = Afp.Nombre,
                [Tipo de Institucion de Prevision] =
                    (
                        CASE
                            WHEN afp.IdSistemaPublico=1 AND afp.IdSSS=0 AND afp.IdEmpart=0 THEN 'Publico'
                            WHEN afp.IdSistemaPublico=1 AND afp.IdSSS=1 AND afp.IdEmpart=0 THEN 'SSS'
                            WHEN afp.IdSistemaPublico=1 AND afp.IdSSS=0 AND afp.IdEmpart=1 THEN 'Empart'
                            ELSE 'Privado' END
                    ),
                [Concepto] = CAST(co.IdConcepto AS NVARCHAR(8)) + ' ' + co.Descripcion,
                [Monto Haber/Descuento $] =
                    (
                        CASE
                            WHEN Afp.idsistemaPublico=1 AND (Afp.IdSSS=1 OR Afp.IdEmpart=1) THEN
                            (
                                CASE
                                    WHEN DetalleLiquidacion.IdConcepto = 504 THEN
                                    (
                                        DetalleLiquidacion.Monto + Liquidacion.MontoAfp
                                    )
                                ELSE DetalleLiquidacion.Monto
                                END
                            )
                            ELSE
                            (
                                CASE
                                    WHEN DetalleLiquidacion.IdConcepto = 504 THEN DetalleLiquidacion.Monto
                                    ELSE DetalleLiquidacion.Monto
                                    END
                            )
                            END
                    ),
                [Monto Haber/Descuento US$] =
                    CASE
                        WHEN ISNULL(
                            (
                                SELECT TOP 1 ValorTipoCambio from ValoresMonedas
                                WHERE Month(FechaTipoCambio) <= Liquidacion.Mes AND Year(FechaTipoCambio) = Liquidacion.Ano AND IdMoneda = 3
                            ), 0
                        ) = 0 THEN 0
                        ELSE
                        DetalleLiquidacion.Monto * (CASE Indicadordebe WHEN 0 THEN -1 ELSE 1 END) / ISNULL((SELECT TOP 1 ValorTipoCambio from ValoresMonedas WHERE
                        Month(FechaTipoCambio) <= Liquidacion.Mes AND Year(FechaTipoCambio) = Liquidacion.Ano
                        AND IdMoneda = 3
                        ), 0)
                        END,
                [Labor] = Actividades.Nombre,
                [Tipo Haber/Descuento] = (CASE WHEN Indicadordebe = 1 THEN 'HABERES' ELSE 'DESCUENTOS' END),
                [Actividad] = FamiliaActividades.Nombre,
                [Cod.Zona] = Liquidacion.IdZona,
                [Zona] = Zona.Nombre,
                [Cuartel] = Cuartel.Nombre, [Fecha Inicio] = c.fechainicio,
                [Tipo Trabajador] = (CASE WHEN C.IdTipo = 1 THEN 'MENSUAL' ELSE 'DIARIO' END),
                [Tipo Contrato] = (CASE WHEN C.IndicadorIndefinido = 0 THEN case when c.FechaTerminoC is null then 'Por Faena' else 'PLAZO FIJO' end ELSE 'INDEFINIDO' END),
                [Tipo Regimen]= tr.descripcion,
                [Dias Trabajados]= Liquidacion.DiasTrabajados,
                [Total Haber] =
                    (
                        CASE
                            WHEN Indicadordebe = 1 and isnull(co.idagrupacion,'') <> 'HORAS'  and co.total=0 and co.IDCONCEPTO<>'150' THEN DetalleLiquidacion.Monto
                            ELSE 0
                            END
                    ),
                (
                    CASE t.IndicadorDeposito WHEN 1 THEN (
                        CASE T.TipoCuentaBancaria WHEN 1 THEN 'Cta. Vista' ELSE 'Cta. Corriente' END
                    )
                    ELSE 'Sin Cuenta'
                    END
                ) AS TipoCuenta,
                T.NumeroCuentaBancaria,Ba.Nombre as Banco ,
                (
                    CASE co.Total WHEN 1 THEN case when co.IDCONCEPTO='101' then 'De Liquidacion' else 'Para Analisis' end ELSE 'De Liquidacion' END
                ) as [Tipo Concepto],
                (
                    CASE WHEN Liquidacion.IdFiniquito <> 0 THEN 'Finiquito' ELSE 'Liquidacion' END
                ) as Tipo  ,
                CASE e.DECIMAL
                    when 0 THEN ltrim(str(Liquidacion.Ruttrabajador)) + '-' + Digito
                    ELSE replicate('0', Tdi.LARGO - Len(Liquidacion.Ruttrabajador))+LEFT(Liquidacion.Ruttrabajador,Tdi.LARGO)
                    END as [Rut Trabajador],
                 horasExtras as [Horas Extras],
                -- A solicitud de Juan Carvacho se agregó este campo
                [Horas Extras Adicionales] = (
                    SELECT SUM(HoraExtrasAdicional) FROM ActividadTrabajador WITH(NOLOCK)
                    WHERE IdEmpresa = C.IdEmpresa AND IdTrabajador = C.IdTrabajador
                    AND MONTH(FechaActividad) = Liquidacion.MES AND YEAR(FechaActividad) = Liquidacion.ANO
                ),
                -- Fin
                diastrabporsueldo as [Dias Trabajados por sueldo],
                [Sueldo Base Ficha]=liquidacion.SUELDOBASEcontrato,
                [Fecha Inicio Periodo]=c.fechainicioperiodo,
                [Fecha Finiquito]=c.fechatermino,
                [Cantidad ] =
                    case
                        WHEN co.IDCONCEPTO='109' THEN (
                            SELECT DIASHABILES FROM  VACACIONES V
                            WHERE (V.IDEMPRESA = LIQUIDACION.IDEMPRESA) AND (V.IDTRABAJADOR = LIQUIDACION.IDTRABAJADOR)
                            AND MONTH(V.FECHAINICIO)=LIQUIDACION.MES AND YEAR(V.FECHAINICIO)=LIQUIDACION.ANO)
                            WHEN co.IDCONCEPTO<>'109' THEN DETALLELIQUIDACION.CANTIDAD
                    END,
                Titulo =
                    CASE
                        WHEN co.INDICADORDEBE=1 AND (co.IDCONCEPTO<>580 ) THEN 'TOTAL INGRESOS'
                        WHEN co.IDCONCEPTO=101  THEN 'A PAGO'
                        WHEN co.INDICADORHABER=1 AND (co.IDCONCEPTO<>210 ) THEN 'TOTAL DESCUENTOS'
                        WHEN co.IDCONCEPTO=210 or co.IDCONCEPTO=580  THEN case when E.Decimal=0  then  'TOTAL DESCUENTOS' else 'TOTAL APORTACIONES' end
                    END,
                Utilidades = CASE WHEN ISNULL(co.DISTRIBUCION,0)=0 THEN 'NO' ELSE 'SI' END,
                T.Sexo,
                T.IdTramo,
                T.IndicadorDeposito,
                Vigencia = (case C.IndicadorVigencia when 1 then 'Si' else 'No' end),
                co.IdAgrupacion,
                [Correlativo Impresion] = CorrImpresion,
                [Oficio] = O.Descripcion ,
                ImponibleTributable = CASE WHEN ISNULL(co.ImponibleTributable,0)=0 THEN 'NO' ELSE 'SI' END,
                Titulo2 =
                    CASE WHEN Indicadordebe = 1 then
                        CASE WHEN ISNULL(co.ImponibleTributable,0)=1 THEN
                            case
                                when co.IDCONCEPTO=100 or co.IDCONCEPTO=400 or co.IDCONCEPTO=160 or co.IDCONCEPTO=180 then 'Imponibles'
                                ELSE 'Otros Imponibles'
                            END else
                        case
                            when co.IDCONCEPTO=150 or co.IDCONCEPTO=199 or co.IDCONCEPTO=506 or co.IDCONCEPTO=507 then ' No Imponibles'
                            ELSE 'Otros No Imponibles' END end
                            else
                            case when co.IDCONCEPTO=200 or co.IDCONCEPTO=205 or co.IDCONCEPTO=207 or co.IDCONCEPTO=210 or co.IDCONCEPTO=211 or co.IDCONCEPTO=211 or co.IDCONCEPTO=230 or co.IDCONCEPTO=250 or co.IDCONCEPTO=261 then ' Legales'
                            ELSE case when co.IdConcepto='101' then ' ' else  'Otros descuentos' end END
                        end,
                liquidacion.Mixta as [Mixta],
                [Cussp]=liquidacion.cussp,
                ltrim(str(liquidacion.IdZonaLabores)) + ' ' + Zl.Nombre as [Zona Labores],  liquidacion.NumeroCuentaBancaria as [Numero Cuenta Bancaria]  ,
                ltrim(str(liquidacion.IdBanco)) + ' ' + ba1.Nombre as [Banco 1]
                --,co.*
            FROM Liquidacion WITH(NOLOCK)
            INNER JOIN Trabajador T WITH(NOLOCK) ON Liquidacion.IdTrabajador = T.IdTrabajador AND Liquidacion.IdEmpresa = T.IdEmpresa
            INNER JOIN Contratos C WITH(NOLOCK) ON Liquidacion.Idempresa=C.IdEmpresa and Liquidacion.Idtrabajador=C.Idtrabajador and Liquidacion.idcontrato=C.IdContrato and Liquidacion.IdZona=C.IdZona
            INNER JOIN Agrupacion Ag ON Ag.IdEmpresa = C.IdEmpresa AND AG.IdAgrupacion = C.IdAgrupacion
            INNER JOIN DetalleLiquidacion WITH(NOLOCK) ON Liquidacion.IdLiquidacion = DetalleLiquidacion.IdLiquidacion
            INNER JOIN ConceptosHaberDescuento co WITH(NOLOCK) ON DetalleLiquidacion.IdConcepto = co.IdConcepto AND DetalleLiquidacion.IdEmpresa = co.IdEmpresa
            INNER JOIN Isapre WITH(NOLOCK) ON Isapre.IdEmpresa = liquidacion.IdEmpresa AND Isapre.IdIsapre = liquidacion.IdIsapre
            INNER JOIN Afp WITH(NOLOCK) ON Afp.IdEmpresa = liquidacion.IdEmpresa AND Afp.IdAfp = liquidacion.IdAfp
            LEFT JOIN Actividades WITH(NOLOCK) ON liquidacion.IdActividadcontrato = Actividades.IdActividad AND liquidacion.IdFamiliacontrato = Actividades.IdFamilia AND liquidacion.IdEmpresa = Actividades.IdEmpresa
            LEFT JOIN FamiliaActividades WITH(NOLOCK) ON Actividades.IdFamilia = FamiliaActividades.IdFamilia AND Actividades.IdEmpresa = FamiliaActividades.IdEmpresa
            LEFT JOIN Zona WITH(NOLOCK) ON liquidacion.IdZona = Zona.IdZona AND liquidacion.IdEmpresa = Zona.IdEmpresa
            LEFT JOIN Zona ZL WITH(NOLOCK) ON liquidacion.IdZonaLabores = Zl.IdZona AND liquidacion.IdEmpresa = Zl.IdEmpresa
            LEFT JOIN Cuartel WITH(NOLOCK) ON liquidacion.IdEmpresa = Cuartel.IdEmpresa AND liquidacion.IdZona = Cuartel.IdZona AND liquidacion.IdCuartelcontrato = Cuartel.IdCuartel
            LEFT JOIN Banco Ba WITH(NOLOCK) ON Ba.IdEmpresa =T.IdEmpresa and Ba.IdBanco=T.IdBanco
            LEFT JOIN Banco Ba1 WITH(NOLOCK) ON Ba1.IdEmpresa =Liquidacion.IdEmpresa and Ba1.IdBanco=Liquidacion.IdBanco
            left join TipoRegimen tr WITH(NOLOCK) on tr.idtipo=Liquidacion.IDRegimenContrato
            INNER JOIN EMPRESA E WITH(NOLOCK) ON E.IDEMPRESA=T.IDEMPRESA
            left join TipoDctoIden tdi on  tdi.idempresa=t.idempresa and tdi.IdTipoDctoIden=t.IdTipoDctoIden
            LEFT JOIN OFICIO O WITH(NOLOCK) ON O.IdEmpresa=T.IdEmpresa AND O.IdOficio=c.IdOficio
            WHERE Liquidacion.IdEmpresa = 9
            --AND (Liquidacion.IdTrabajador=NULL OR NULL IS NULL)
            AND Liquidacion.IdTrabajador = '022227'
            AND (
                co.Total = 0 or DetalleLiquidacion.idconcepto in (251,287,505,504,101,560,581,141,288,285,286,248,503) -- modificado mavc 28/07/2014
            )
            AND CAST('01/' + LTRIM(STR(Liquidacion.mes)) + '/' + LTRIM(STR(Liquidacion.Ano)) as smalldatetime) BETWEEN '01/09/2020' AND '30/09/2020'
            AND Liquidacion.IDZONA IN (SELECT * FROM dbo.TblArr('63'))
            "
        );
    }
}
