<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Planilla extends Model
{
    protected $table = 'Liquidacion';

    public static function get(int $empresaId = 9, $periodo, $zonasLaborId = [])
    {
        $periodo = Carbon::parse($periodo);
        $mes     = $periodo->month;
        $anio    = $periodo->year;

        $result = DB::table('Liquidacion as l')
            ->select(
                'idLiquidacion as id',
                'mes as mes',
                'ano as anio',
                'l.idEmpresa as empresa_id',
                'l.idZona as zona_id',
                DB::raw("'1' as tipo_pago_id"),
                DB::raw('CAST(ROUND(MontoAPagar, 2, 0) as decimal(18, 2)) as monto'),
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS trabajador_id
                "),
                'b.Nombre as banco',
                't.NumeroCuentaBancaria as numero_cuenta',
                DB::raw('CONVERT(varchar, c.FechaInicioPeriodo, 23) fecha_inicio'),
                //'c.FechaInicioPeriodo as fecha_inicio',
                't.nombre as trabajador_nombre',
                't.apellidoPaterno as trabajador_apellido_paterno',
                't.apellidoMaterno as trabajador_apellido_materno',
                'c.Jornal as trabajador_jornal',
                'c.IdRegimen as trabajador_regimen_id',
                'o.Descripcion as trabajador_oficio',
                DB::raw('
                    CASE
                        WHEN c.IdRegimen = 2
                            THEN CAST(ROUND(c.SueldoBase, 2, 0) as decimal(18, 2))
                        WHEN c.IdRegimen = 3
                            THEN CAST(ROUND(c.SueldoBase * 1.2638 * 30, 2, 0) as decimal(18, 2))
                        ELSE
                            CAST(ROUND(c.SueldoBase * 1.2638, 2, 0) as decimal(18, 2))
                    END AS trabajador_sueldo_bruto
                '),
            )
            ->join('Trabajador as t', [
                'l.idEmpresa'    => 't.idEmpresa',
                'l.idTrabajador' => 't.idTrabajador'
            ])
            ->join('Banco as b', [
                'b.idBanco'   => 't.idBanco',
                'b.idEmpresa' => 't.idEmpresa'
            ])
            ->join('Contratos as c', [
                'c.IdEmpresa' => 'l.IdEmpresa',
                'l.IdContrato' => 'c.IdContrato'
            ])
            ->join('Oficio as o', [
                'o.IdEmpresa' => 'c.IdEmpresa',
                'o.IdOficio' => 'c.IdOficio'
            ])
            ->where('l.idempresa', $empresaId)
            ->where('mes', $mes)
            ->where('ano', $anio)
            ->when(sizeof($zonasLaborId) !== 0, function ($query) use ($zonasLaborId) {
                $query->whereIn('t.IdZonaLabores', $zonasLaborId);
            })->get();

        return $result;
    }

    public static function getDetalle(int $empresaId = 9, $periodo, $zonasLaborId = [])
    {
        $periodo = Carbon::parse($periodo);
        $mes     = $periodo->month;
        $anio    = $periodo->year;

        $result = DB::table('Liquidacion as l')
            ->select(
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
                    ) AS DECIMAL(18, 2)) as monto
                "),
                DB::raw("(CASE WHEN Indicadordebe = 1 THEN 1 ELSE 0 END) as tipo")
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
            ->join('Trabajador as t', [
                'l.idEmpresa'    => 't.idEmpresa',
                'l.idTrabajador' => 't.idTrabajador'
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
            ->where('co.Descripcion', 'not like', '%SEMANA CORRIDA%')
            ->when(sizeof($zonasLaborId) !== 0, function ($query) use ($zonasLaborId) {
                $query->whereIn('t.IdZonaLabores', $zonasLaborId);
            })->get();

        return $result;
    }

    /*
        * A		PERSONAL AUSENTE
        * F		FALTA JUSTIFICADA
        * FE	FERIADO
        * P		PERSONAL PERMISOS CON
        * PS	PERSONAL PERMISOS SIN
        * D		PERSONAL DESCANSOS MEDICOS
        * DS	PERSONAL DESCANSOS MEDICOS CON SUBSIDIO
        * PT	PATERNIDAD
        * M		MATERNIDAD
        * V		PERSONAL DE VACACIONES
        * S		PERSONAL SUSPENDIDOS
        * SP	PERSONAL CON S.P.L
        * RV	RENUNCIA VOLUNTARIA
        * TC	TERMINO DE CONTRATO
        * SPP	SUSPENSION PERIODO DE PRUEBA
        * DPF	DESPIDO POR IMPUTACION DE FALTAS GRAVES
        * AT	ABANDONO DE TRABAJO
        * CF	FALLECIMIENTO
    */
    public static function getHorasJornal(int $empresaId = 9, $periodo, $regimenId)
    {
        $periodo = Carbon::parse($periodo);
        $fechaPrimerDia = $periodo->firstOfMonth()->format('d-m-Y');

        try {
            $result = DB::connection('sqlsrv')->select("SPC_INFORME_HORAS_MENSUALES @EMPRESA = $empresaId, @REGIMEN = $regimenId, @FECHAPRIMERDIA = '$fechaPrimerDia'");
            return [
                'mes'  => $periodo->month,
                'anio' => $periodo->year,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getHorasNoJornal(int $empresaId = 9, $periodo)
    {
        $periodo        = Carbon::parse($periodo);
        $mes            = $periodo->month;
        $anio           = $periodo->year;
        $fechaPrimerDia = clone $periodo->firstOfMonth();
        $fechaUltimoDia = clone $periodo->lastOfMonth();

        $permisosQuery = DB::table('Liquidacion as a')
            ->select(
                //'c.RutTrabajador as trabajador_id',
                //'at.IdActividad as id',
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS trabajador_id
                "),
                DB::raw('Cast(p.FechaInicio as date) as fecha'),
                'p.HoraInasistencia as horas',
                DB::raw("
                    CASE
                        WHEN p.MotivoAusencia = 'FALTA' THEN 'A'
                        WHEN P.MOTIVOAUSENCIA = 'PERSONAL SUSPENDIDOS' THEN 'S'
                        WHEN P.MOTIVOAUSENCIA = 'PERSONAL CON S.P.L' THEN 'SP'
                        WHEN P.MOTIVOAUSENCIA = 'PERMISO' THEN 'PS'
                        WHEN P.MOTIVOAUSENCIA = 'PERMISO CON' THEN 'P'
                        WHEN P.MOTIVOAUSENCIA = 'FALTA JUSTIFICADA' THEN 'F'
                        ELSE (
                            CASE WHEN P.MOTIVOAUSENCIA='LICENCIA' AND P.TIPOLICENCIA = 6 THEN 'PT'
                            WHEN P.MOTIVOAUSENCIA='LICENCIA' AND P.TIPOLICENCIA=2 THEN 'M'
                            WHEN P.MOTIVOAUSENCIA='LICENCIA' AND P.TIPOLICENCIA<>2 AND P.TIPOLICENCIA<>6  AND P.IMPORTE='1 Basico Diario'THEN 'D'
                            WHEN P.MOTIVOAUSENCIA='LICENCIA' AND P.TIPOLICENCIA<>2 AND P.TIPOLICENCIA<>6 AND P.IMPORTE='2 Subsidio Diario' THEN 'DS'
                            END
                        )
                        END AS motivo
                "),
                'p.IndicadorRemuneracion as con_goce'
            )
            ->join('Trabajador as t', [
                'a.IdEmpresa'    => 't.IdEmpresa',
                'a.IdTrabajador' => 't.IdTrabajador'
            ])
            ->join('Contratos as c', [
                'a.IdEmpresa'    => 'c.IdEmpresa',
                'a.IdContrato' => 'c.IdContrato'
            ])
            ->leftJoin('PermisosInasistencias as p', [
                'c.RutTrabajador' => 'p.RutTrabajador'
            ])
            ->whereDate('p.FechaInicio', '>=', $fechaPrimerDia)
            ->whereDate('p.FechaInicio', '<=', $fechaUltimoDia)
            ->where('c.jornal', false)
            //->where('p.IndicadorRemuneracion', false)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId);

        $tmpPermisos = $permisosQuery->get()->toArray();

        $vacacionesQuery = DB::table('Liquidacion as l')
            ->select(
                'v.IdVacacion as vacacion_id',
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS trabajador_id
                "),
                DB::raw('Cast(v.FechaInicio as date) as fecha_inicio'),
                DB::raw('Cast(v.FechaFinal as date) as fecha_fin'),
                'c.IdContrato as contrato_id',
                'l.IdLiquidacion as liquidacion_id',
                'l.idEmpresa as empresa_id',
                //DB::raw('Cast(p.FechaInicio as date) as fecha'),
            )
            ->join('Trabajador as t', [
                'l.IdEmpresa'    => 't.IdEmpresa',
                'l.idTrabajador' => 't.idTrabajador'
            ])
            ->join('Contratos as c', [
                'c.idEmpresa' => 'l.idEmpresa',
                'c.idContrato' => 'l.idContrato'
            ])
            ->join('Vacaciones as v', [
                'v.idEmpresa' => 'l.idEmpresa',
                'v.idTrabajador' => 'l.idTrabajador'
            ])
            ->where('c.jornal', false)
            ->where('l.Mes', $mes)
            ->where('l.Ano', $anio)
            ->where('l.IdEmpresa', $empresaId)
            ->where(function($query) use ($fechaPrimerDia, $fechaUltimoDia) {
                $query->whereBetween('v.FechaInicio', [$fechaPrimerDia->format('Ymd h:i:s'), $fechaUltimoDia->format('Ymd h:i:s')]);
                $query->orWhereBetween('v.FechaFinal', [$fechaPrimerDia->format('Ymd h:i:s'), $fechaUltimoDia->format('Ymd h:i:s')]);
            });

        $vacaciones = $vacacionesQuery->get();

        $tmpVacaciones = [];
        foreach ($vacaciones as $vacacion) {
            $fechaFin = Carbon::parse($vacacion->fecha_fin)->lessThanOrEqualTo($fechaUltimoDia) ? $vacacion->fecha_fin : $fechaUltimoDia;
            $period = CarbonPeriod::create($vacacion->fecha_inicio, $fechaFin);

            foreach ($period as $p) {
                array_push($tmpVacaciones, [
                    'fecha' => $p->toDateString(),
                    'horas' => 8,
                    'trabajador_id' => $vacacion->trabajador_id,
                    'motivo' => 'V',
                    'con_goce' => "1"
                ]);
            }
        }

        $contratosTerminados = DB::table('Liquidacion as l')
            ->select(
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS trabajador_id
                "),
                DB::raw("cast(c.FechaTermino as date) as fecha_termino")
            )
            ->join('Trabajador as t', [
                'l.IdEmpresa'    => 't.IdEmpresa',
                'l.idTrabajador' => 't.idTrabajador'
            ])
            ->join('Contratos as c', [
                'c.idEmpresa' => 'l.idEmpresa',
                'c.idContrato' => 'l.idContrato'
            ])
            ->where('l.IdEmpresa', $empresaId)
            ->where('c.jornal', false)
            ->where('l.Mes', $mes)
            ->where('l.Ano', $anio)
            ->whereBetween('c.FechaTermino', [$fechaPrimerDia->format('Ymd h:i:s'), $fechaUltimoDia->format('Ymd h:i:s')])
            ->get()->toArray();

            $tmpContratosTerminados = [];
            foreach ($contratosTerminados as $contratoTerminado) {
                $periodo = CarbonPeriod::create($contratoTerminado->fecha_termino, $fechaUltimoDia);

                foreach ($periodo as $p) {
                    array_push($tmpContratosTerminados, [
                        'fecha' => $p->toDateString(),
                        'horas' => 8,
                        'trabajador_id' => $contratoTerminado->trabajador_id,
                        'motivo' => 'A',
                        'con_goce' => '0'
                    ]);
                }
            }

        $aunSinContrato = DB::table('Liquidacion as l')
            ->select(
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS trabajador_id
                "),
                DB::raw("cast(c.FechaInicioPeriodo as date) as fecha_inicio")
            )
            ->join('Trabajador as t', [
                'l.IdEmpresa'    => 't.IdEmpresa',
                'l.idTrabajador' => 't.idTrabajador'
            ])
            ->join('Contratos as c', [
                'c.idEmpresa' => 'l.idEmpresa',
                'c.idContrato' => 'l.idContrato'
            ])
            ->where('l.IdEmpresa', $empresaId)
            ->where('c.jornal', false)
            ->where('l.Mes', $mes)
            ->where('l.Ano', $anio)
            ->whereBetween('c.FechaInicioPeriodo', [$fechaPrimerDia->format('Ymd h:i:s'), $fechaUltimoDia->format('Ymd h:i:s')])
            ->get()->toArray();

        $tmpAunSinContrato = [];
        foreach ($aunSinContrato as $contrato) {
            $hasta = Carbon::parse($contrato->fecha_inicio)->subDay();
            $periodo = CarbonPeriod::create($fechaPrimerDia, $hasta);

            foreach ($periodo as $p) {
                array_push($tmpAunSinContrato, [
                    'fecha' => $p->toDateString(),
                    'horas' => 8,
                    'trabajador_id' => $contrato->trabajador_id,
                    'motivo' => 'A',
                    'con_goce' => '0'
                ]);
            }
        }

        return [
            'mes' => $mes,
            'anio' => $anio,
            'data' => [ ...$tmpPermisos, ...$tmpVacaciones, ...$tmpContratosTerminados, ...$tmpAunSinContrato ]
            //'data' => [ ...$tmpAunSinContrato ]
        ];
    }
}
