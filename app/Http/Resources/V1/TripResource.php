<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city' =>$this->city,
            'start_date' =>$this->start_date,
            'end_date' =>$this->end_date,
            'description' =>$this->description,
            'created_at' =>$this->created_at,
            'updated_at' =>$this->updated_at,
        ];
    }
}
