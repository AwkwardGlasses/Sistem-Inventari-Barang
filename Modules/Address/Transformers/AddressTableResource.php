<?php

namespace Modules\Address\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressTableResource extends JsonResource
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
            'province' => $this->province,
            'regency'  => $this->regency,
            'district' => $this->district,
            'exact_location' => $this->exact_location,
            'zip_code' => $this->zip_code
        ];
    }
}
