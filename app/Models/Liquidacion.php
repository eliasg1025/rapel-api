<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Liquidacion extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.Liquidacion';

    public $incrementing = false;
}
