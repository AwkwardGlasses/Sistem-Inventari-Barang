<?php

namespace Modules\Employee\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

use Modules\Employee\Transformers\PositionTableResource;
use Modules\Address\Transformers\AddressTableResource;
use App\Http\Resources\UserTableResource;
class EmployeeTableResource extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'position'  => new PositionTableResource($this->position),
            'address'   => new AddressTableResource($this->address),
            'account'   => new UserTableResource($this->user)
        ];
    }
}
