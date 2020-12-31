<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contrato extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Contratos';

    public $incrementing = false;

    public function zona_labor()
    {
        return $this->belongsTo('App\Models\ZonaLabor');
    }

    public static function byTrabajador($empresa_id, $dni)
    {
        return self::where([
            'IdEmpresa' => $empresa_id,
            'RutTrabajador' => $dni
        ])->get();
    }

    public static function byPeriodo($rut)
    {
        $contratos = DB::table('dbo.Contratos as c')
            ->select(
                'c.IdEmpresa as empresa_id',
                'Periodo as periodo',
                'IdContrato as contrato_id',
                'IndicadorVigencia as indicador_vigencia',
                DB::raw('CAST(ROUND(c.SueldoBase * 1.2638, 2, 0) as decimal(18, 2)) sueldo_bruto'),
                're.Descripcion as regimen',
                'z.Nombre as zona_labor',
                'o.Descripcion as oficio',
                'i.Texto as inciso',
                'FechaInicio as fecha_inicio',
                'FechaTerminoC as fecha_termino_c',
                'FechaInicioPeriodo as fecha_inicio_periodo',
                'FechaTermino as fecha_termino',
                DB::raw('CONVERT(varchar, FechaInicioPeriodo, 23) desde'),
                DB::raw('CONVERT(varchar, FechaTermino, 23) hasta'),
                DB::raw('DATEDIFF(MONTH, c.FechaInicioPeriodo, c.FechaTermino) as meses')
            )
            ->join('dbo.TipoRegimen as re', 're.IdTipo', '=', 'c.IdRegimen')
            ->join('dbo.Oficio as o', [
                'o.IdOficio' => 'c.IdOficio',
                'o.IdEmpresa' => 'c.IdEmpresa'
            ])
            ->join('dbo.Zona as z', [
                'z.IdZona' => 'c.IdZona',
                'z.IdEmpresa' => 'c.IdEmpresa'
            ])
            ->leftJoin('dbo.Inciso as i', [
                'i.IdArticulo' => 'c.IdArticulo',
                'i.IdInciso' => 'c.IdInciso'
            ])
            ->where('RutTrabajador', $rut)
            ->where(function($query) {
                $query->whereNotNull('c.FechaTermino')
                    ->orWhere('c.IndicadorVigencia', '1');
            })
            ->orderBy('c.FechaInicio', 'DESC')
            ->get();

        return $contratos;
    }

    public static function activo($rut, $activo=true, $info_jornal=false)
    {
        $where = $activo ? [
            'RutTrabajador' => $rut,
            'IndicadorVigencia' => '1'
        ] : ['RutTrabajador' => $rut];

        $contratos = self::where($where)
            ->select(
                'IdContrato as contrato_id',
                'IdTrabajador as trabajador_code',
                'IdEmpresa as empresa_id',
                'FechaInicioPeriodo as fecha_inicio',
                'IdZona as zona_id',
                'Jornal as jornal',
                'FechaTerminoC as fecha_termino_c',
                'IdAfp as afp_id',
                'IdOficio as oficio_id',
                'IdCuartel as cuartel_id',
                'IdRegimen as regimen_id',
                DB::raw('
                    CASE
                        WHEN IdRegimen = 2
                            THEN CAST(ROUND(SueldoBase, 2, 0) as decimal(18, 2))
                        WHEN IdRegimen = 3
                            THEN CAST(ROUND(SueldoBase * 1.2638 * 30, 2, 0) as decimal(18, 2))
                        ELSE
                            CAST(ROUND(SueldoBase * 1.2638, 2, 0) as decimal(18, 2))
                    END AS sueldo_bruto
                '),
            )
            ->orderBy('FechaInicio', 'DESC')
            ->get();

        if ($info_jornal == false) {
            return $contratos;
        }

        function getJornal($contrato, $rut) {
            if ( $contrato->jornal == '0' ) {
                return $contrato;
            } else {
                try {
                    $ultima_actividad = ActividadTrabajador::getUltimoDiaLaborado($rut);

                    if ( $ultima_actividad ) {
                        if ( Carbon::parse($ultima_actividad[0]->fecha_actividad)->diffInDays(Carbon::now()) <= 2 ) {
                            $contrato->cuartel_id = $ultima_actividad[0]->cuartel_id;
                            $contrato->zona_id = $ultima_actividad[0]->zona_labor_id;
                        }
                    }

                    /**
                     * Esto es suponiendo que cada representacion de cuartel en zonas 6X tenga su contraparte en zonas 5X
                     */
                    $name = ZonaLabor::where([
                        'IdEmpresa' => $contrato->empresa_id,
                        'IdZona' => $contrato->zona_id
                    ])->first()->Nombre;

                    $name = trim(explode('(', $name)[0]);
                    //dd($name);
                    $zona_labor = ZonaLabor::whereIn('IdEmpresa', ['9', '14'])->where('Nombre', 'like', '%' . $name . '%')->where('Nombre', 'not like', '%OBREROS%')->first();
                    if ($zona_labor) {
                        $contrato->zona_id = $zona_labor->IdZona;
                    }

                    return $contrato;
                } catch (\Exception $e) {
                    return $contrato;
                }
            }
        }

        foreach ($contratos as &$contrato) {
            $contrato = getJornal($contrato, $rut);
        };

        return $contratos;
    }
}
