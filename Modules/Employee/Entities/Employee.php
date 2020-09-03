<?php

namespace Modules\Employee\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    #has one position
    public function position(){
        return $this->belongsTo('Modules\Employee\Entities\Position');
    }

    #has one address
    public function address(){
        return $this->belongsTo('Modules\Address\Entities\Address');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
