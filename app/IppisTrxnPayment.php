<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IppisTrxnPayment extends Model
{
    protected $dates = ['deduction_for', 'value_date'];

     public function scopeIsAuthorized($query)
    {
        return $query->where('is_authorized', 1);
    }
}
