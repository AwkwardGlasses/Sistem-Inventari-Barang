<?php

namespace Modules\Address\Entities;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    public $timestamps = false;

    public function regencies()
    {
        return $this->hasMany('Modules\Address\Entities\Regency');
    }

    public function addresses()
    {
        return $this->hasMany('Modules\Address\Entities\Address');
    }
}
