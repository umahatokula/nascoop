<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessingFee extends Model
{
    protected $casts = [
        'amount' => 'double',
    ];
}
