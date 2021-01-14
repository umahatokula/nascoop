<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\IPPISExportFileGenerated' => [
            'App\Listeners\SaveExportFileInDB',
        ],
        'App\Events\TransactionOccured' => [
            // 'App\Listeners\SaveToAccountTrxn',
            'App\Listeners\SaveToLedgerTrxn',
        ],
        'App\Events\LoanAuthorized' => [
            'App\Listeners\SaveToAccountTrxn',
            'App\Listeners\SaveToLedgerTrxn',
        ],
        'App\Events\LoanRepayment' => [
            'App\Listeners\SaveToAccountTrxn',
            'App\Listeners\SaveToLedgerTrxn',
        ],
        'App\Events\DepositToSavings' => [
            'App\Listeners\SaveToAccountTrxn',
            'App\Listeners\SaveToLedgerTrxn',
        ],
        'App\Events\WithdrawalFromSavings' => [
            'App\Listeners\SaveToAccountTrxn',
            'App\Listeners\SaveToLedgerTrxn',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
