<?php

namespace Modules\Supplier\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Address\Transformers\AddressTableResource;

class SupplierTableResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'contact' => $this->contact,
            // 'pics' => new SupplierPicTableResourceCollection($this->supplier_pics),
            'address' => new AddressTableResource($this->address)
        ];
    }
}
