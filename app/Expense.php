<?php

namespace App;
use App\Expense;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $dates = ['date'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'is_authorized' => 'boolean',
    ];


    /**
     * Generata an entry code that will be used for all entries in ledger, ltl payments, stl payments and commodities payments
     */
    function generateTrxnNumber() {

        // The length we want the unique reference number to be
        $trxnNumber_length = 20;

        // A true/false variable that lets us know if we've found a unique reference number or not
        $trxnNumber_found = false;

        // Define possible characters. Characters that may be confused such as the letter 'O' and the number zero aren't included
        $possible_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

        // Until we find a unique reference, keep generating new ones
        while (!$trxnNumber_found) {

        // Start with a blank reference number
        $trxnNumber = "";

        // Set up a counter to keep track of how many characters have currently been added
        $i = 0;

        // Add random characters from $possible_chars to $trxnNumber until $trxnNumber_length is reached
        while ($i < $trxnNumber_length) {

            // Pick a random character from the $possible_chars list
            $char = substr($possible_chars, mt_rand(0, strlen($possible_chars)-1), 1);

            $trxnNumber .= $char;

            $i++;

        }

        // Our new unique reference number is generated. Lets check if it exists or not
        $result = Expense::where('trxn_number', $trxnNumber)->first();

        if (is_null($result)) {

            // We've found a unique number. Lets set the $trxnNumber_found variable to true and exit the while loop
            $trxnNumber_found = true;

        }

        return $trxnNumber;

        }
        
    }
    

    /**
     * Generate PV number for Expense
     */
    public function pvNumberGenerator() {
        $pvNumber = PVNumber::where('trxn_type', 'expense')->latest('id')->first();

        $pvN = $pvNumber ? $pvNumber->pv_number + 1 : 1;

        // make pv number entry
        $pv = new PVNumber;
        $pv->pv_number = $pvN;
        $pv->trxn_type = 'expense';
        $pv->generated_by = auth()->user()->ippis;
        $pv->save();

        return 'EXP/'.date('Y').'/'.$pvN;
    }
}
