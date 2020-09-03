<?php

namespace Modules\Category\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;    
    protected $guarded = [];

     #could be use by many asset
     public function asset(){
        return $this->hasMany('Modules\Asset\Entities\Asset');
    }
}
