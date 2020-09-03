<?php

namespace Modules\Record\Entities;

use Illuminate\Database\Eloquent\Model;


class Record extends Model
{
    protected $guarded = [];

    const REQ_RULES = [
        'asset_id' => 'required',
        'supplier_id' => 'required',
        'price' => 'required|numeric',
        'arrival_date' => 'required',
    ];

    public function asset() {
        return $this->belongsTo('Modules\Asset\Entities\Asset');
    }

    public function supplier() {
        return $this->belongsTo('Modules\Supplier\Entities\Supplier');
    }
}
