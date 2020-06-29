<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Agrupacion;
use App\Models\Cuartel;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Labor;
use App\Models\Nacionalidad;
use App\Models\Oficio;
use App\Models\Provincia;
use App\Models\Regimen;
use App\Models\TipoContrato;
use App\Models\TipoVia;
use App\Models\TipoZona;
use App\Models\ZonaLabor;

class DataController extends Controller
{
    public function __construct()
    {
       //
    }

    public function porEmpresa()
    {
        $nacionalidades = Nacionalidad::_all();
        $tipos_zonas = TipoZona::_all();
        $tipos_vias = TipoVia::_all();
        $zonas_labor = ZonaLabor::_all();
        $oficios = Oficio::_all();
        $regimenes = Regimen::_all();
        $actividades = Actividad::_all();
        $cuarteles = Cuartel::_all();
        $agrupaciones = Agrupacion::_all();
        $labores = Labor::_all();
        $tipos_contratos = TipoContrato::_all();

        $data = [
            'nacionalidades' => $nacionalidades,
            'tipos_zonas'    => $tipos_zonas,
            'tipos_vias'     => $tipos_vias,
            'zonas_labor'    => $zonas_labor,
            'oficios'        => $oficios,
            'regimenes'      => $regimenes,
            'actividades'    => $actividades,
            'cuarteles'      => $cuarteles,
            'agrupaciones'   => $agrupaciones,
            'labores'        => $labores,
            'tipo_contrato'  => $tipos_contratos
        ];

        return response()->json([
            'message' => 'Datos obtenidos',
            'data' => $data
        ], 200);
    }

    public function localidades()
    {
        $departamentos = Departamento::_get();
        $provincias = Provincia::_all();
        $distritos = Distrito::_all();

        $data = [
            'departamentos' => $departamentos,
            'provincias' => $provincias,
            'distritos' => $distritos
        ];

        return response()->json([
            'message' => 'Datos obtenidos',
            'data' => $data
        ], 200);
    }
}
