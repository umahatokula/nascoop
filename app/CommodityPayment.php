<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommodityPayment extends Model
{
    protected $dates = ['loan_date', 'deposit_date'];

    public function scopeIsAuthorized($query)
    {
        return $query->where('is_authorized', 1);
    }
    
    public function member()
    {
        return $this->belongsTo(Member::class, 'ippis', 'ippis');
    }
    
    public function commodity()
    {
        return $this->belongsTo(Commodity::class, 'commodity_id', 'id');
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType_Ext::class, 'trxn_type', 'xact_type_code_ext');
    }
}
