<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    
    protected $table = "invoices";

    protected $guarded =[
        'id'
    ];

    public function section(){

        return $this->belongsTo('App\Section');
    }

}
