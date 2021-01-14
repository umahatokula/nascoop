<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InitialImport extends Model
{
    protected $casts = [
    	'imports' => 'json',
    ];
}
