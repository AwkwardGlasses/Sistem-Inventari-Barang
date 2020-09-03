<?php

namespace Modules\Address\Entities;

use Illuminate\Database\Eloquent\Model;

class Regency extends Model
{
    public $timestamps = false;

    public function province()
    {
        return $this->belongsTo('Modules\Address\Entities\Province');
    }

    public function districts()
    {
        return $this->hasMany('Modules\Address\Entities\District');
    }

    public function addresses()
    {
        return $this->hasMany('Modules\Address\Entities\Address');
    }
}
