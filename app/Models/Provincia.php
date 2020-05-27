<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $connection = 'sqlsrv2';

    protected $table = 'dbo.PROVINCIAS';

    public static function _show($codigo)
    {
        return self::where([
            'COD_PAIS' => 'PE',
            'COD_PROVC' => $codigo
        ])->first();
    }

    public static function _distritos($codigo)
    {
        $distritos = Distrito::where([
            'COD_PAIS' => 'PE',
            'COD_PROVC' => $codigo
        ])->get();

        return [
            'provincia' => self::_show($codigo),
            'distritos' => $distritos
        ];
    }
}
