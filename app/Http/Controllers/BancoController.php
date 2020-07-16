<?php


namespace App\Http\Controllers;


use App\Models\Banco;

class BancoController extends Controller
{
    public function all()
    {
        $data = Banco::_all();

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }

    public function get($id_empresa)
    {
        $data = Banco::_get($id_empresa);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
