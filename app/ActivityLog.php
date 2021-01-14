<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{

	/**
	 * Get member
	 */
	public function member()
	{
			return $this->belongsTo(Member::class, 'ippis', 'ippis');
	}

	/**
	 * Get done by
	 */
	public function performed_by()
	{
			return $this->belongsTo(Member::class, 'done_by', 'ippis');
	}

	/**
	 * make log entry
	 */
    public function logThis($trxn_number, $ippis, $activity, $amount, $is_authorized, $done_by) {
    	$this->trxn_number       = $trxn_number;
    	$this->ippis            = $ippis;
    	$this->activity         = $activity;
    	$this->amount           = $amount;
    	$this->is_authorized    = $is_authorized;
    	$this->done_by          = $done_by;
      $this->save();
        
      return $this;
    }
}
