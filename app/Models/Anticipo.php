<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Anticipo extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Anticipos';

    public static function trabajadorAnticipos( int $empresaId, $periodo, $zonaLaborId = [] )
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        $query = DB::table('Anticipos as a')
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
                'b.Nombre as banco',
                't.NumeroCuentaBancaria as numero_cuenta',
                'c.Jornal as jornal'
            )
            ->join('Contratos as c', [
                'c.IdEmpresa' => 'a.IdEmpresa',
                'a.IdTrabajador' => 'c.IdTrabajador'
            ])
            ->join('Trabajador as t', [
                'a.IdEmpresa'    => 't.IdEmpresa',
                'a.IdTrabajador' => 't.IdTrabajador'
            ])
            ->join('Banco as b', [
                'b.IdEmpresa' => 't.IdEmpresa',
                'b.idBanco' => 't.IdBanco'
            ])
            ->where('c.IndicadorVigencia', 1)
            //->distinct('t.RutTrabajador')
            ->when(sizeof($zonaLaborId) !== 0, function ($query) use ($zonaLaborId) {
                $query->whereIn('a.IdZona', $zonaLaborId);
            })
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId);

        //return $query->get();

        foreach($query->cursor() as $row) {
            yield $row;
        }
    }

    /**
     * Obtiene los dias que falta un trabajador sin goce, los demas dias se considera que fue (excepto domingos)
     */
    public static function trabajadorHorasSinDigitacionGenerator ( int $empresaId, string $periodo, $zonasLaborId = [] )
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        $fechaInicio = $periodo->firstOfMonth();
        $fechaFin = $periodo->day(15);

        foreach (
            DB::table('Anticipos as a')
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
                'a.IdTrabajador' => 'c.IdTrabajador'
            ])
            ->leftJoin('PermisosInasistencias as p', [
                'c.RutTrabajador' => 'p.RutTrabajador'
            ])
            ->whereDate('p.FechaInicio', '>=', '2020-10-01')
            ->whereDate('p.FechaInicio', '<=', '2020-10-16')
            ->where('c.IndicadorVigencia', true)
            ->where('c.jornal', false)
            ->where('p.IndicadorRemuneracion', false)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId)
            ->when(sizeof($zonasLaborId) !== 0, function ($query) use ($zonasLaborId) {
                $query->whereIn('a.IdZona', [$zonasLaborId]);
            })
            ->cursor() as $row
        ) {
            yield $row;
        }
    }

    public static function trabajadorHorasConDigitacionGenerator ( int $empresaId, string $periodo, $zonasLaborId = [] )
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        $fechaInicio = $periodo->firstOfMonth();
        $fechaFin = $periodo->day(15);

        $first = DB::table('Anticipos as a')
            ->select(
                //'at.IdActividadTrabajador as id',
                DB::raw("
                    CASE
                        WHEN t.IdTipoDctoIden = 1
                            THEN RIGHT('000000' + CAST(t.RutTrabajador as varchar), 8)
                        ELSE
                            RIGHT('000000' + CAST(t.RutTrabajador as varchar), 9)
                    END AS trabajador_id
                "),
                DB::raw('Cast(at.FechaActividad as date) as fecha'),
                'at.HoraNormales as horas',
            )
            ->join('Trabajador as t', [
                'a.IdEmpresa'    => 't.IdEmpresa',
                'a.IdTrabajador' => 't.IdTrabajador'
            ])
            ->join('Contratos as c', [
                'a.IdEmpresa'    => 'c.IdEmpresa',
                'a.IdTrabajador' => 'c.IdTrabajador'
            ])
            ->leftJoin('ActividadTrabajador as at', [
                'at.RutTrabajador' => 'c.RutTrabajador'
            ])
            ->whereDate('at.FechaActividad', '>=', '2020-10-01')
            ->whereDate('at.FechaActividad', '<=', '2020-10-16')
            ->where('c.IndicadorVigencia', true)
            ->where('c.jornal', true)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId)
            ->whereIn('a.IdZona', $zonasLaborId);

        $second = DB::table('Anticipos as a')
            ->select(
                //'c.RutTrabajador as trabajador_id',
                //'p.IdPermiso as id',
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
                'a.IdTrabajador' => 'c.IdTrabajador'
            ])
            ->leftJoin('PermisosInasistencias as p', [
                'c.RutTrabajador' => 'p.RutTrabajador'
            ])
            ->whereDate('p.FechaInicio', '>=', '2020-10-01')
            ->whereDate('p.FechaInicio', '<=', '2020-10-16')
            ->where('c.IndicadorVigencia', true)
            ->where('c.jornal', true)
            ->where('p.IndicadorRemuneracion', true)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId)
            ->whereIn('a.IdZona', $zonasLaborId);

        foreach (
            $first->union($second)->cursor() as $row
        ) {
            yield $row;
        }
    }

    public static function trabajadorHorasConDigitacionPGenerator ( int $empresaId, string $periodo, $zonaLaborId = 0 )
    {
        $periodo = Carbon::parse($periodo);

        $mes  = $periodo->month;
        $anio = $periodo->year;

        $fechaInicio = $periodo->firstOfMonth();
        $fechaFin = $periodo->day(15);

        $second = DB::table('Anticipos as a')
            ->select(
                //'c.RutTrabajador as trabajador_id',
                //'p.IdPermiso as id',
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
                'a.IdTrabajador' => 'c.IdTrabajador'
            ])
            ->leftJoin('PermisosInasistencias as p', [
                'c.RutTrabajador' => 'p.RutTrabajador'
            ])
            ->whereDate('p.FechaInicio', '>=', '2020-10-01')
            ->whereDate('p.FechaInicio', '<=', '2020-10-16')
            ->where('c.IndicadorVigencia', true)
            ->where('c.jornal', true)
            ->where('p.IndicadorRemuneracion', true)
            ->where('a.Mes', $mes)
            ->where('a.Ano', $anio)
            ->where('a.IdEmpresa', $empresaId)
            ->when($zonaLaborId != 0, function ($query) use ($zonaLaborId) {
                $query->where('a.IdZona', $zonaLaborId);
            });

        foreach (
            $second->cursor() as $row
        ) {
            yield $row;
        }
    }
}
