<?php

namespace Modules\Supplier\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Address\Transformers\AddressTableResource;

class SupplierPicTableResource extends JsonResource
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
            'supplier' => new SupplierTableResource($this->supplier),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => new AddressTableResource($this->address),
            'active' => $this->active
        ];
    }
}
