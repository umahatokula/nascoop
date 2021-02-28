<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\DateScope;
use Carbon\Carbon;

class LedgerInternalTransaction extends Model
{
    protected $dates = ['value_date'];

    /**
     * Convert date_time to human readable format.
     *
     * @param  string  $value
     * @return string
     */
    public function getDateTimeAttribute($value)
    {
        $date_array = explode(" ", $value);
        $date = date("Y-m-d H:i:s", $date_array[1]);

        return Carbon::parse($date);
    }

    public function ledger_dr()
    {
        return $this->belongsTo(Ledger_Internal::class, 'ledger_no_dr', 'ledger_no');
    }

    public function ledger_cr()
    {
        return $this->belongsTo(Ledger_Internal::class, 'ledger_no', 'ledger_no');
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new DateScope);
    }
}
