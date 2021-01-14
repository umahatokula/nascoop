<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use Carbon\Carbon;

class AccountTransaction extends Model
{
    
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

    // public function 
}
