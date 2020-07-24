<?php

namespace App\Http\Controllers;

use App\Models\MotivoPermiso;

class MotivoPermisoController extends Controller
{
    public function get($id_empresa)
    {
        $data = MotivoPermiso::_get($id_empresa);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
