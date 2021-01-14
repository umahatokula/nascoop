<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempActivityLog extends Model
{
    protected $casts = ['logs' => 'array'];
}
