<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShareSetting extends Model
{

    protected $fillable = ['*'];

    protected $dates = ['open_date', 'close_date'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rate' => 'double',
    ];
}
