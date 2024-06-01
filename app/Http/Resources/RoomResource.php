<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public $preserveKeys = true;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'admin_id' => $this->admin_id,
            'number' => $this->number,
            'custome_number' => $this->custome_number,
            'title' => $this->title??"",
            'group' => $this->group,
            'desc' => $this->desc ?? "",
        ];
    }
}
