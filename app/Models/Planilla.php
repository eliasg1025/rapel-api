<?php

namespace App\Models;

use Carbon\Carbon;
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
                'o.Descripcion as trabajador_oficio'
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
            ->when(sizeof($zonasLaborId) !== 0, function ($query) use ($zonasLaborId) {
                $query->whereIn('t.IdZonaLabores', $zonasLaborId);
            })->get();

        return $result;
    }

    public static function getHorasJornal(int $empresaId = 9, $periodo, $zonasLaborId = [])
    {
        $periodo = Carbon::parse($periodo);
        $mes     = $periodo->month;
        $anio    = $periodo->year;

        $result = DB::table('Liquidacion as a')
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
            ->whereDate('p.FechaInicio', '>=', '2020-10-01')
            ->whereDate('p.FechaInicio', '<=', '2020-10-16')
            ->where('c.jornal', false)
            ->where('p.IndicadorRemuneracion', false)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId)
            ->when(sizeof($zonasLaborId) !== 0, function ($query) use ($zonasLaborId) {
                $query->whereIn('t.IdZonaLabores', [$zonasLaborId]);
            })
            ->get();

        return $result;
    }

    public static function getHorasNoJornal(int $empresaId = 9, $periodo, $zonasLaborId = [])
    {
        $periodo = Carbon::parse($periodo);
        $mes     = $periodo->month;
        $anio    = $periodo->year;

        $result = DB::table('Liquidacion as a')
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
            ->whereDate('p.FechaInicio', '>=', '2020-10-01')
            ->whereDate('p.FechaInicio', '<=', '2020-10-16')
            ->where('c.jornal', false)
            ->where('p.IndicadorRemuneracion', false)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId)
            ->when(sizeof($zonasLaborId) !== 0, function ($query) use ($zonasLaborId) {
                $query->whereIn('t.IdZonaLabores', [$zonasLaborId]);
            })
            ->get();

        return $result;
    }
}
