<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class UnidadesMedidasController extends Controller
{
    public function index($empresaId)
    {
        $data = DB::table('dbo.UnidadMedida')
            ->select(
                'IdEmpresa as empresa_id',
                'Descripcion as name',
                'IdUnidad as id'
            )
            ->where('IdEmpresa', '=', $empresaId)
            ->get();

        return response()->json([
            'message' => 'Data recuperada',
            'data' => $data
        ]);
    }
}
