<?php

namespace Modules\Record\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Asset\Transformers\AssetTableResource;
use Modules\Supplier\Transformers\SupplierTableResource;

class RecordTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'asset' => new AssetTableResource($this->asset),
            'supplier' => new SupplierTableResource($this->supplier),
            'ref_code' => $this->ref_code,
            'arrival_date' => $this->arrival_date,
            'price' => $this->price
        ];
    }
}
