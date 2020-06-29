<?php


namespace App\Http\Controllers;

use App\Models\Labor;

class LaborController extends Controller
{
    public function get($id_empresa, $id_actividad)
    {
        $data = Labor::_get($id_empresa, $id_actividad);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
