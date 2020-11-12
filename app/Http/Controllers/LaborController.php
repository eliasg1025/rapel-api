<?php


namespace App\Http\Controllers;

use App\Models\Labor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaborController extends Controller
{
    public function index($empresaId, Request $request)
    {
        $unidadMedidaId = $request->query('unidadMedida');

        //dd($unidadMedidaId);

        $data = DB::table('dbo.Actividades as a')
            ->select(
                'a.IdActividad as id',
                'a.IdEmpresa as empresa_id',
                'a.Nombre as name',
                'a.UnidadMedida as unidad_medida_id'
            )
            ->where('a.IdEmpresa', '=', $empresaId)
            ->when($unidadMedidaId !== '' && $unidadMedidaId != 0, function($query) use ($unidadMedidaId) {
                $query->where('a.UnidadMedida', $unidadMedidaId);
            })
            ->get();

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ]);
    }

    public function get($id_empresa, $id_actividad)
    {
        $data = Labor::_get($id_empresa, $id_actividad);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
