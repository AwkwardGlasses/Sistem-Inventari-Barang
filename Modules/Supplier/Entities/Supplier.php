<?php

namespace Modules\Supplier\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $guarded = ['deleted_at'];

    public function address()
    {
        return $this->belongsTo('Modules\Address\Entities\Address');
    }

    public function supplier_pics()
    {
        return $this->hasMany('Modules\Supplier\Entities\SupplierPic');
    }

    public function records()
    {
        return $this->hasMany('Modules\Record\Entities\Record');
    }
}
