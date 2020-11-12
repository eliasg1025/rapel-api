<?php


namespace App\Http\Controllers;

use App\Models\Cuartel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuartelController extends Controller
{
    public function index($empresaId, Request $request)
    {
        $zonaId = $request->query('zonaId');
        $variedadId = $request->query('variedadId');

        $data = DB::table('dbo.Cuartel as c')
            ->select(
                'c.IdCuartel as id',
                'c.IdZona as zona_labor_id',
                'c.IdEmpresa as empresa_id',
                'c.Nombre as name',
                'c.IdVariedad as variedad_id'
            )
            ->where('c.IdEmpresa', $empresaId)
            ->when($variedadId !== '' && $variedadId !== '0', function($query) use ($variedadId) {
                $query->where('c.IdVariedad', $variedadId);
            })
            ->when($zonaId !== '' && $zonaId != 0, function($query) use ($zonaId) {
                $query->where('c.IdZona', '=', $zonaId);
            })
            ->get();

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }

    public function get($id_empresa, $id_zona_labor)
    {
        $data = Cuartel::_get($id_empresa, $id_zona_labor);

        return response()->json([
            'message' => 'Data obtenida',
            'data' => $data
        ], 200);
    }
}
