<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Anticipo extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Anticipos';

    public static function get( int $empresaId, $periodo, $zonasLaborId = [] )
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        $result = DB::table('Anticipos as a')
            ->select(
                "idAnticipo as id",
                'a.mes as mes',
                'a.ano as anio',
                'a.idEmpresa as empresa_id',
                't.IdZonaLabores as zona_id',
                DB::raw("'2' as tipo_pago_id"),
                DB::raw('CAST(ROUND(a.Monto, 2, 0) as decimal(18, 2)) as monto'),
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
                'a.IdEmpresa'    => 't.IdEmpresa',
                'a.IdTrabajador' => 't.IdTrabajador'
            ])
            ->join('Banco as b', [
                'b.IdEmpresa' => 't.IdEmpresa',
                'b.idBanco' => 't.IdBanco'
            ])
            ->join('Contratos as c', [
                'c.IdEmpresa' => 'a.IdEmpresa',
                'a.IdTrabajador' => 'c.IdTrabajador'
            ])
            ->join('Oficio as o', [
                'o.IdEmpresa' => 'c.IdEmpresa',
                'o.IdOficio' => 'c.IdOficio'
            ])
            ->where('c.IndicadorVigencia', 1)
            //->distinct('t.RutTrabajador')
            ->where('a.IdEmpresa', $empresaId)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->when(sizeof($zonasLaborId) !== 0, function ($query) use ($zonasLaborId) {
                $query->whereIn('t.IdZonaLabores', $zonasLaborId);
            })->get();

        return $result;
    }

    public static function getDetalle(int $empresaId, $periodo, $zonasLaborId = [])
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        $anticipos = DB::select(
            "
            SELECT
                A.IdAnticipo,A.IdTrabajador as [Id Trabajador],
                A.Monto AS Monto,
                AD.SueldoAlaFecha,
                AD.TotalFeriados,
                AD.HoraExtra,
                AD.TotalCargas,
                AD.TotalDomingos,
                AD.Bono_Labor,
                AD.HaberesImponibles,
                ad.CtsAgraria,
                ad.GratificacionAgraria,
                AD.BonoPacking,

                AD.MontoAfp,
                AD.Impuesto,
                ad.OtrosDescuentos
            FROM (
                SELECT
                    V.idempresa, V.IdTrabajador,
                    Idcontrato=MAX(V.Idcontrato), V.Ano, V.Mes, HrsDia=SUM(V.HRSNORM), HrsNoct=SUM(V.HRSNOCT), HrsEx25=SUM(V.HRSEX25), HrsEx35=SUM(V.HrsEx35),
                    HrsExNoc25=SUM(V.HrsExNoc25), HrsExNoc35=SUM(V.HrsExNoc35)
                FROM VIEW_HORAS_DIAS V WHERE V.IdEmpresa=$empresaId AND V.IdZona IN(SELECT NUMBER FROM dbo.TblArr('0,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,90,0')) AND (V.IDTRABAJADOR=NULL OR NULL IS NULL) AND (V.ANO=$anio OR $anio IS NULL)
                AND (V.MES=$mes OR $mes IS NULL) and V.CorteDias=1-- solo  tomar hasta el 15
                GROUP BY V.idempresa, V.IdTrabajador, V.Ano, V.Mes
            ) croos
            INNER JOIN Anticipos A WITH(NOLOCK) ON A.IdEmpresa=croos.IdEmpresa AND A.IdTrabajador=croos.IdTrabajador AND A.Ano=croos.Ano AND A.Mes=croos.Mes
            LEFT JOIN AnticiposDetalle AD WITH(NOLOCK) ON AD.IDEMPRESA=A.IDEMPRESA AND AD.IDTRABAJADOR=A.IDTRABAJADOR AND AD.FECHA=A.FECHA
            INNER JOIN EMPRESA E WITH(NOLOCK) ON E.IDEMPRESA=croos.IDEMPRESA
            INNER JOIN Trabajador T WITH(NOLOCK) ON croos.IdTrabajador = T.IdTrabajador AND croos.IdEmpresa = T.IdEmpresa
            "
        );

        $detalles = [];
        foreach ($anticipos as $row)
        {
            //dd($row);
            array_push($detalles, [
                'id'            => $row->IdAnticipo . '001',
                'concepto'      => 'SUELDO A LA FECHA',
                'monto'         => round($row->SueldoAlaFecha, 2),
                'tipo'          => 1,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            array_push($detalles, [
                'id'            => $row->IdAnticipo . '002',
                'concepto'      => 'TOTAL FERIADOS',
                'monto'         => round($row->TotalFeriados, 2),
                'tipo'          => 1,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            array_push($detalles, [
                'id'            => $row->IdAnticipo . '003',
                'concepto'      => 'TOTAL HORAS EXTRA',
                'monto'         => round($row->HoraExtra, 2),
                'tipo'          => 1,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            if ($row->TotalCargas > 0) {
                array_push($detalles, [
                    'id'            => $row->IdAnticipo . '004',
                    'concepto'      => '150 ASIG. FAMILIAR',
                    'monto'         => round($row->TotalCargas, 2),
                    'tipo'          => 1,
                    'liquidacion_id'       => $row->IdAnticipo,
                    'tipo_pago_id'  => '2'
                ]);
            }

            array_push($detalles, [
                'id'            => $row->IdAnticipo . '005',
                'concepto'      => 'TOTAL DOMINGOS',
                'monto'         => round($row->TotalDomingos, 2),
                'tipo'          => 1,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            if ($row->Bono_Labor > 0) {
                array_push($detalles, [
                    'id'            => $row->IdAnticipo . '006',
                    'concepto'      => 'BONO LABOR',
                    'monto'         => round($row->Bono_Labor, 2),
                    'tipo'          => 1,
                    'liquidacion_id'       => $row->IdAnticipo,
                    'tipo_pago_id'  => '2'
                ]);
            }

            array_push($detalles, [
                'id'            => $row->IdAnticipo . '007',
                'concepto'      => 'CTS AGRARIA',
                'monto'         => round($row->CtsAgraria, 2),
                'tipo'          => 1,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            array_push($detalles, [
                'id'            => $row->IdAnticipo . '008',
                'concepto'      => 'GRATIFIACION AGRARIA',
                'monto'         => round($row->GratificacionAgraria, 2),
                'tipo'          => 1,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            array_push($detalles, [
                'id'            => $row->IdAnticipo . '009',
                'concepto'      => 'TOTAL AFP / ONP',
                'monto'         => round($row->MontoAfp, 2),
                'tipo'          => 0,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            array_push($detalles, [
                'id'            => $row->IdAnticipo . '010',
                'concepto'      => 'OTROS DESCUENTOS',
                'monto'         => round($row->OtrosDescuentos, 2),
                'tipo'          => 0,
                'liquidacion_id'       => $row->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);

            if ($row->Impuesto > 0) {
                array_push($detalles, [
                    'id'            => $row->IdAnticipo . '011',
                    'concepto'      => 'IMPUESTO 5TA CATEGORIA',
                    'monto'         => round($row->Impuesto, 2),
                    'tipo'          => 0,
                    'liquidacion_id'       => $row->IdAnticipo,
                    'tipo_pago_id'  => '2'
                ]);
            }
        }

        $bonos = DB::select("
            SELECT
            dl.*,
            con.Descripcion,
            l.Mes, l.Ano
            ,a.IdAnticipo
            FROM [bsis_rem_afr].[dbo].[Liquidacion] as l
            inner join [dbo].[Anticipos] as a on a.IdEmpresa = l.IdEmpresa and a.Mes = l.Mes and a.Ano = l.Ano and a.IdTrabajador = l.IdTrabajador
            inner join [dbo].[Detalleliquidacion] as dl on dl.IdLiquidacion = l.IdLiquidacion and dl.IdEmpresa = l.IdEmpresa
            inner join [dbo].[ConceptosHaberDescuento] as con on con.IdEmpresa = dl.IdEmpresa and con.IdConcepto = dl.IdConcepto
            where l.IdEmpresa = $empresaId and l.Mes = $mes and l.Ano = $anio and (con.Descripcion like '%BONO%' or con.Descripcion like '%REINTEGRO%')
        ");
        
        foreach ($bonos as $bono) {
            array_push($detalles, [
                'id'            => $bono->IdDetalle,
                'concepto'      => $bono->Descripcion,
                'monto'         => round($bono->Monto, 2),
                'tipo'          => 1,
                'liquidacion_id'       => $bono->IdAnticipo,
                'tipo_pago_id'  => '2'
            ]);
        }

        return $detalles;
    }

    public static function getHorasNoJornal(int $empresaId = 9, $periodo)
    {
        $periodo        = Carbon::parse($periodo);
        $mes            = $periodo->month;
        $anio           = $periodo->year;
        $fechaPrimerDia = clone $periodo->firstOfMonth();
        $fechaUltimoDia = clone $periodo->lastOfMonth();

        $permisosQuery = DB::table('Anticipos as a')
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
                'a.IdTrabajador' => 'c.IdTrabajador'
            ])
            ->leftJoin('PermisosInasistencias as p', [
                'c.RutTrabajador' => 'p.RutTrabajador'
            ])
            ->whereDate('p.FechaInicio', '>=', $fechaPrimerDia)
            ->whereDate('p.FechaInicio', '<=', $fechaUltimoDia)
            ->where('c.jornal', false)
            ->where('c.IndicadorVigencia', 1)
            //->where('p.IndicadorRemuneracion', false)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId);

        $tmpPermisos = $permisosQuery->get()->toArray();

        $vacacionesQuery = DB::table('Anticipos as a')
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
                'a.IdAnticipo as liquidacion_id',
                'a.idEmpresa as empresa_id',
                //DB::raw('Cast(p.FechaInicio as date) as fecha'),
            )
            ->join('Trabajador as t', [
                'a.IdEmpresa'    => 't.IdEmpresa',
                'a.idTrabajador' => 't.idTrabajador'
            ])
            ->join('Contratos as c', [
                'c.idEmpresa' => 'a.idEmpresa',
                'c.idTrabajador' => 'a.idTrabajador'
            ])
            ->join('Vacaciones as v', [
                'v.idEmpresa' => 'a.idEmpresa',
                'v.idTrabajador' => 'a.idTrabajador'
            ])
            ->where('c.jornal', false)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId)
            ->where('c.IndicadorVigencia', 1)
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

        $contratosTerminados = DB::table('Anticipos as a')
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
                'a.IdEmpresa'    => 't.IdEmpresa',
                'a.idTrabajador' => 't.idTrabajador'
            ])
            ->join('Contratos as c', [
                'c.idEmpresa' => 'a.idEmpresa',
                'c.idTrabajador' => 'a.idTrabajador'
            ])
            ->where('a.IdEmpresa', $empresaId)
            ->where('c.jornal', false)
            ->where('c.IndicadorVigencia', 1)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
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

        $aunSinContrato = DB::table('Anticipos as a')
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
                'a.IdEmpresa'    => 't.IdEmpresa',
                'a.idTrabajador' => 't.idTrabajador'
            ])
            ->join('Contratos as c', [
                'c.idEmpresa' => 'a.idEmpresa',
                'c.idTrabajador' => 'a.idTrabajador'
            ])
            ->where('a.IdEmpresa', $empresaId)
            ->where('c.jornal', false)
            ->where('c.IndicadorVigencia', 1)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
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
        ];
    }
}
