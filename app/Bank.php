<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'transfer_charge' => 'double',
    ];
    
}
