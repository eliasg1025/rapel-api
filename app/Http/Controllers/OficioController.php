<?php


namespace App\Http\Controllers;

use App\Models\Oficio;

class OficioController extends Controller
{
    public function get($id_empresa)
    {
        $oficios = Oficio::_get($id_empresa);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $oficios
        ], 200);
    }
}
