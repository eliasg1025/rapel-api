<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class VariedadesController extends Controller
{
    public function index($empresaId)
    {
        $data = DB::table('dbo.Variedad as v')
            ->select(
                'v.IdEmpresa as empresa_id',
                'v.IdVariedad as id',
                DB::raw("(e.Nombre + ' - ' + v.Nombre) as name")
            )
            ->join('dbo.Especie as e', [
                'e.IdEspecie' => 'v.IdEspecie',
                'e.IdEmpresa' => 'v.IdEmpresa'
            ])
            ->where('v.IdEmpresa', '=', $empresaId)
            ->get();

        return response()->json([
            'message' => 'Data recuperada',
            'data' => $data
        ]);
    }
}
