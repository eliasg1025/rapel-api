<?php


namespace App\Http\Controllers;


use App\Models\Afp;

class AfpController extends Controller
{
    public function get($id_empresa)
    {
        $data = Afp::_get($id_empresa);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
