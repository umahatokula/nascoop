<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\ActivityLog;

class ActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $trxnNumber;
    public $ippis;
    public $description;
    public $savingsAmount;
    public $isAuthorized;
    public $doneBy;
    // public $logs = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($trxnNumber, $ippis, $description, $savingsAmount, $isAuthorized, $doneBy)
    // public function __construct($logs)
    {
        $this->trxnNumber = $trxnNumber;
        $this->ippis = $ippis;
        $this->description = $description;
        $this->savingsAmount = $savingsAmount;
        $this->isAuthorized = $isAuthorized;
        $this->doneBy = $doneBy;
        // $this->logs = $logs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $activityLog = new ActivityLog;
        $activityLog->logThis($this->trxnNumber, $this->ippis, $this->description, $this->savingsAmount, $this->isAuthorized, $this->doneBy);

        $message = [$this->trxnNumber, $this->ippis, $this->description, $this->savingsAmount, $this->isAuthorized, $this->doneBy];
        \Log::info($message);

        // dd($logs);
        // foreach ($logs as $log) {
        //     foreach ($log as $entry) {
        //         foreach ($entry as $value) {
        //             $this->trxnNumber = $value[0];
        //             $this->ippis = $value[1];
        //             $this->description = $value[2];
        //             $this->savingsAmount = $value[3];
        //             $this->isAuthorized = $value[4];
        //             $this->doneBy = $value[5];

        //             $activityLog = new ActivityLog;
        //             $activityLog->logThis($this->trxnNumber, $this->ippis, $this->description, $this->savingsAmount, $this->isAuthorized, $this->doneBy);

        //             $message = [$this->trxnNumber, $this->ippis, $this->description, $this->savingsAmount, $this->isAuthorized, $this->doneBy];
        //             \Log::info($message);
        //         }
        //     }
            
        // }
    }
}
