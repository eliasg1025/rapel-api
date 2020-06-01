<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Nacionalidad;
use App\Models\Provincia;
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

        $data = [
            'nacionalidades' => $nacionalidades,
            'tipos_zonas' => $tipos_zonas,
            'tipos_vias' => $tipos_vias,
            'zonas_labor' => $zonas_labor,
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
