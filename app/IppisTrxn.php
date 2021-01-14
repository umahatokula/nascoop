<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IppisTrxn extends Model
{
    protected $dates = ['deduction_for'];

    public function center() {
        return $this->belongsTo(Center::class, 'center_id', 'id');
    }


    public function payments() {
        return $this->hasMany(IppisTrxnPayment::class, 'ippis_trxn_id', 'id');
    }
}
