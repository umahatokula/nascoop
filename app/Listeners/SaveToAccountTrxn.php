<?php

namespace App\Listeners;

use App\Events\LoanAuthorized;
// use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\AccountTransaction;

class SaveToAccountTrxn
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LoanAuthorized  $event
     * @return void
     */
    public function handle($event)
    {
        // dd($event);
        AccountTransaction::insert([
            'ledger_no'          => $event->ledger_no_1,
            'date_time'          => $event->date_time,
            'xact_type_code'     => $event->xact_type_code,
            'xact_type_code_ext' => $event->xact_type_code_ext,
            'account_no'         => $event->account_no,
            'account_type_ext'   => $event->account_type_ext,
            'amount'             => $event->amount,
            'description'        => $event->description,
        ]);
    }
}
