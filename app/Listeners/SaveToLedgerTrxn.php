<?php

namespace App\Listeners;

use App\Events\LoanAuthorized;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\LedgerInternalTransaction;

class SaveToLedgerTrxn
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
        LedgerInternalTransaction::insert([
            'ledger_no'          => $event->ledger_no_1,
            'date_time'          => $event->date_time,
            'ledger_no_dr'       => $event->ledger_no_2,
            'amount'             => $event->amount,
            'description'        => $event->description,
        ]);
    }
}
