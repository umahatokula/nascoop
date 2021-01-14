<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DepositToSavings
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ledger_no_1;
    public $ledger_no_2;
    public $date_time;
    public $xact_type_code;
    public $xact_type_code_ext;
    public $account_no;
    public $account_type_ext;
    public $amount;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($ledger_no_1, $ledger_no_2, $date_time, $xact_type_code, $xact_type_code_ext, $account_no, $account_type_ext, $amount)
    {
        $this->ledger_no_1        = $ledger_no_1;
        $this->ledger_no_2        = $ledger_no_2;
        $this->date_time          = $date_time;
        $this->xact_type_code     = $xact_type_code;
        $this->xact_type_code_ext = $xact_type_code_ext;
        $this->account_no         = $account_no;
        $this->account_type_ext   = $account_type_ext;
        $this->amount             = $amount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
