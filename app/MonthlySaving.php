<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class MonthlySaving extends Model
{
    public function payments()
    {
        return $this->hasMany(MonthlySavingsPayment::class);
    }

    public function doneBy()
    {
        return $this->belongsTo(Member::class, 'done_by', 'ippis');
    }
    
    /**
     * Get total monthly savings
     */
    public function totalBalance1($payPoint = null) {
        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('ledgers')->get();
        } else {
            $members = Member::with('ledgers')->get();
        }
        
        $bal = $members->reduce(function ($carry, $member) {
            $bal = $member->monthly_savings_payments->where('is_authorized', 1)->last() ? $member->monthly_savings_payments->where('is_authorized', 1)->last()->bal : 0;
            return $carry + $bal;
        });
        dd($payPoint, $bal);
    }

    public function totalBalance($payPoint = null) {
        $members = [];

        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('ledgers')->select('ippis')->get()->toArray();

            return DB::table('monthly_savings_payments')
                    ->join('members', 'members.ippis', '=', 'monthly_savings_payments.ippis')
                    ->where('is_authorized', 1)
                    ->whereIn('monthly_savings_payments.ippis', $members)
                    ->sum('bal');

        } else {

            return DB::table('monthly_savings_payments')
                    ->join('members', 'members.ippis', '=', 'monthly_savings_payments.ippis')
                    ->where('is_authorized', 1)
                    ->sum('bal');

        }
    }
}
