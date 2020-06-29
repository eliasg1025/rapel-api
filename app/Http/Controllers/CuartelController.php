<?php


namespace App\Http\Controllers;

use App\Models\Cuartel;

class CuartelController extends Controller
{
    public function get($id_empresa, $id_zona_labor)
    {
        $data = Cuartel::_get($id_empresa, $id_zona_labor);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
