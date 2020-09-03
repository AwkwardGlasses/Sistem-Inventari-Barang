<?php

namespace Modules\Address\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    const REQUEST_RULES = [
        'province_id' => 'required',
        'regency_id' => 'required',
        'district_id' => 'required',
        'exact_location' => 'required',
        'zip_code' => ''
    ];

    public function province()
    {
        return $this->belongsTo('Modules\Address\Entities\Province');
    }

    public function regency()
    {
        return $this->belongsTo('Modules\Address\Entities\Regency');
    }

    public function district()
    {
        return $this->belongsTo('Modules\Address\Entities\District');
    }

    public function employees()
    {
        return $this->hasMany('Modules\Employee\Entities\Employee');
    }

    public function suppliers()
    {
        return $this->hasMany('Modules\Supplier\Entities\Supplier');
    }

    public function supplier_pics()
    {
        return $this->hasMany('Modules\Supplier\Entities\SupplierPic');
    }
}
