<?php

namespace Modules\Employee\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    #could be use by many employee
    public function employee(){
        return $this->hasMany('Modules\Employee\Entities\Employee');
    }
}
