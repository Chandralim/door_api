<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomActivityResource extends JsonResource
{
    public $preserveKeys = true;

    public function toArray($request)
    {
        return [
            'room_id' => $this->room_id,
            // 'room'=>new RoomResource($this->whenLoaded('room')),
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
