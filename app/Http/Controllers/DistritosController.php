<?php

namespace App\Http\Controllers;

use App\Models\Distrito;

class DistritosController extends Controller
{
    public function __construct()
    {
        //
    }

    public function show($codigo)
    {
        $distrito = Distrito::_provincia($codigo);

        $conditional = empty($distrito);

        return response()->json([
            'message' => $conditional ? 'No se encontro el distrito' : 'Distrito obtenido',
            'data' => $distrito
        ], $conditional ? 404 : 200);
    }
}
