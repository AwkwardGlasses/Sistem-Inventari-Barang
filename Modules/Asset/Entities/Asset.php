<?php

namespace Modules\Asset\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    protected $guarded = [];
    use SoftDeletes;

    #has one category
    public function category(){
        return $this->belongsTo('Modules\Category\Entities\Category');
    }

    public function records()
    {
        return $this->hasMany('Modules\Record\Entities\Record');
    }
}
