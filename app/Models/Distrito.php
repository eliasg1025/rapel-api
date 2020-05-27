<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    protected $connection = 'sqlsrv2';

    protected $table = 'dbo.COMUNAS';

    public static function _show($codigo)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_COM' => $codigo
        ])->first();
    }

    public static function _provincia($codigo)
    {
        $distrito = self::_show($codigo);
        $provincia = Provincia::_show($distrito->COD_PROVC);
        $departamento = Departamento::_show($provincia->COD_REG);

        return [
            'distrito' => $distrito,
            'provincia' => $provincia,
            'departamento' => $departamento
        ];
    }
}
