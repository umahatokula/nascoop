<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\HasCompositePrimaryKey;

class IppisExportData extends Model
{
    use HasCompositePrimaryKey;
    
    protected $fillable = ['*'];

    protected $primaryKey = ['ippis', 'month', 'year'];
    
    public $incrementing = false;

}
