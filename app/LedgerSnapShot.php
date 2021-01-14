<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LedgerSnapShot extends Model
{
    protected $casts = [
    	'exports' => 'json',
    ];

	/**
	 * Get done by
	 */
	public function performed_by()
	{
			return $this->belongsTo(Member::class, 'done_by', 'ippis');
	}

	/**
	 * Get done by
	 */
	public function center()
	{
			return $this->belongsTo(Center::class, 'pay_point_id', 'id');
	}
}
