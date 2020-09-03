<?php

namespace Modules\Address\Entities;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public $timestamps = false;

    public function regency()
    {
        return $this->belongsTo('Modules\Address\Entities\Regency');
    }

    public function addresses()
    {
        return $this->hasMany('Modules\Address\Entities\User_address');
    }
}
