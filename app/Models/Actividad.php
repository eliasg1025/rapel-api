<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $connection = 'sqlsrv';

    //
    protected $table = 'dbo.Cuartel';
}
