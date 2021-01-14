<?php

namespace App\Listeners;

use App\Events\IPPISExportFileGenerated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\IppisDeductionsExport;


class SaveExportFileInDB
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
     * @param  IPPISExportFileGenerated  $event
     * @return void
     */
    public function handle(IPPISExportFileGenerated $event)
    {        
        IppisDeductionsExport::insert($event->deductions);
    }
}
