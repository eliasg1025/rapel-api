<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoZona extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TipoZona';
}
