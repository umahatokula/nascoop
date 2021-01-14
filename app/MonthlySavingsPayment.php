<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MonthlySavingsPayment extends Model
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
        // 'is_authorized' => 'boolean',
        // 'is_withdrawal' => 'boolean',
    ];
    
    protected $dates = ['withdrawal_date', 'deposit_date'];

    public function scopeIsAuthorized($query)
    {
        return $query->where('is_authorized', 1);
    }
    
    public function monthlySavings()
    {
        return $this->belongsTo(MonthlySavings::class);
    }
    
    public function member()
    {
        return $this->belongsTo(Member::class, 'ippis', 'ippis');
    }

    public function monthlySaving()
    {
        return $this->belongsTo(MonthlySaving::class, 'monthly_saving_id', 'id');
    }

    public function doneBy()
    {
        return $this->belongsTo(Member::class, 'done_by', 'ippis');
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType_Ext::class, 'trxn_type', 'xact_type_code_ext');
    }
    

    /**
     * Generate PV number for Withdrawals/Refunds
     */
    public function pvNumberGenerator() {
        $pvNumber = PVNumber::where('trxn_type', 'Withdrawals_Refunds')->latest('id')->first();

        $pvN = $pvNumber ? $pvNumber->pv_number + 1 : 1;

        // make pv number entry
        $pv = new PVNumber;
        $pv->pv_number = $pvN;
        $pv->trxn_type = 'Withdrawals_Refunds';
        $pv->generated_by = auth()->user()->ippis;
        $pv->save();

        return 'WR/'.date('Y').'/'.$pvN;
    }
}
