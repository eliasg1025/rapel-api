<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oficio extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Oficio';
}
