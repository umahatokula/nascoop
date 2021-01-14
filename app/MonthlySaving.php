<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    public function totalBalance($payPoint = null) {
        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('ledgers')->get();
        } else {
            $members = Member::with('ledgers')->get();
        }
        // $members = Member::with('ledgers')->get();
        
        $total = $members->reduce(function ($carry, $member) {
            $bal = $member->ledgers->where('is_authorized', 1)->last() ? $member->ledgers->where('is_authorized', 1)->last()->savings_bal : 0;
            return $carry + $bal;
        });

        return $total;
    }
}
