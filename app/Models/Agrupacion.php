<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agrupacion extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Agrupacion';
}
