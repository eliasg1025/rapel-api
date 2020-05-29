<?php

namespace App\Http\Controllers;

use App\Models\ZonaLabor;

class ZonaLaborController extends Controller
{
    public function __construct()
    {
        //
    }

    public function get($id_empresa)
    {
        $zonas_labores = ZonaLabor::_get($id_empresa);

        return response()->json([
            'message' =>  sizeof($zonas_labores) === 0 ? 'No se encontraron zonas de labores' : 'Zonas de labores obtenidos',
            'data' => $zonas_labores,
        ], sizeof($zonas_labores) === 0 ? 404 : 200);
    }
}
