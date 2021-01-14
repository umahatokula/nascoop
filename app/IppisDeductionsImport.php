<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IppisDeductionsImport extends Model
{
    protected $casts = [
    	'imports' => 'array',
    	'reconciled' => 'json',
    	'is_done' => 'boolean',
    	'is_successful' => 'boolean',
    ];

    // protected $dates = ['deduction_for'];

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
    
    public function getTotalDeductionAttribute() {
        return $this->attributes['total_deduction'] / 100;
    }
    
    public function setTotalDeductionAttribute($value) {
        return $this->attributes['total_deduction'] = $value * 100;
    }
}
