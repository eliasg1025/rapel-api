<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoContrato extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.TipoContrato';
}
