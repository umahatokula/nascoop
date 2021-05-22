<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ManualLedgerPosting extends Model
{

    public function member()
    {
        return $this->belongsTo(Member::class, 'ippis', 'ippis');
    }
}
