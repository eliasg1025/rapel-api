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

    public static function activo($rut, $activo=true, $info_jornal=false)
    {
        $where = $activo ? [
            'RutTrabajador' => $rut,
            'IndicadorVigencia' => '1'
        ] : ['RutTrabajador' => $rut];

        $contratos = self::where($where)
            ->select(
                'IdContrato as contrato_id',
                'IdEmpresa as empresa_id',
                'FechaInicioPeriodo as fecha_inicio',
                'IdZona as zona_id',
                'Jornal as jornal',
                'FechaTerminoC as fecha_termino_c',
                'IdAfp as afp_id',
                'IdOficio as oficio_id',
                'IdCuartel as cuartel_id',
                'IdRegimen as regimen_id'
            )
            ->orderBy('FechaInicioPeriodo', 'DESC')
            ->get();

        if ($info_jornal == false) {
            return $contratos;
        }

        function getJornal($contrato, $rut) {
            if ( $contrato->jornal == '0' ) {
                return $contrato;
            } else {
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
                $zona_labor = ZonaLabor::whereIn('IdEmpresa', ['9', '14'])->where('Nombre', 'like', '%' . $name . '%')->where('Nombre', 'not like', '%OBREROS%')->first();
                //dd($zona_labor);
                if ($zona_labor) {
                    $contrato->zona_id = $zona_labor->IdZona;
                }

                return $contrato;
            }
        }

        foreach ($contratos as &$contrato) {
            $contrato = getJornal($contrato, $rut);
        };

        return $contratos;
    }
}
