<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'university_id' => $this->university_id,
            'usertype' => $this->usertype,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
                'code' => $this->department?->code,
            ],
            'program' => [
                'id' => $this->program?->id,
                'name' => $this->program?->name,
                'code' => $this->program?->code,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
