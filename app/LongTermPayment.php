<?php

namespace App;
use App\Member;

use Illuminate\Database\Eloquent\Model;

class LongTermPayment extends Model
{

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'dr' => 'double',
        'cr' => 'double',
        'bal' => 'double',
    ];
    
    protected $dates = ['loan_date', 'deposit_date'];

    public function scopeIsAuthorized($query)
    {
        return $query->where('is_authorized', 1);
    }
    
    public function member()
    {
        return $this->belongsTo(Member::class, 'ippis', 'ippis');
    }
    
    public function longTermLoan()
    {
        return $this->belongsTo(LongTerm::class, 'long_term_id', 'id');
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType_Ext::class, 'trxn_type', 'xact_type_code_ext');
    }
}
