<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";
    
    protected $guarded =[
        'id'
    ];

   
    public function section(){

        return $this->belongsTo('App\Section');
    }


}
