<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IppisDeductionsExport extends Model
{
    protected $casts = [
    	'exports' => 'json',
    	'reconciled' => 'json',
    	'is_done' => 'boolean',
    ];

    protected $dates = ['deduction_for'];

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
			return $this->belongsTo(Center::class);
	}
    
    public function member() {
        return $this->belongsTo(Member::class, 'ippis', 'ippis');
    }
}
