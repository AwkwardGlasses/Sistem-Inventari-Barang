<?php

namespace Modules\Supplier\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPic extends Model
{
    use SoftDeletes;
    protected $guarded = ['deleted_at'];

    public function address()
    {
        return $this->belongsTo('Modules\Address\Entities\Address');
    }

    public function supplier()
    {
        return $this->belongsTo('Modules\Supplier\Entities\Supplier');
    }
}
