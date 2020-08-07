<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Contratos';

    public $incrementing = false;

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
            if ( $contrato->jornal == 0 ) {
                return $contrato;
            } else {
                $ultima_actividad = ActividadTrabajador::getUltimoDiaLaborado($rut);

                if ($ultima_actividad) {
                    $contrato->cuartel_id = $ultima_actividad[0]->cuartel_id;
                    $contrato->zona_id = $ultima_actividad[0]->zona_labor_id;
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
