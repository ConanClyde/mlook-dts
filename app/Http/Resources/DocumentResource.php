<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'tracking_number' => $this->tracking_number,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'approval_status' => $this->approval_status,
            'urgency_level' => $this->urgency_level,
            'urgency_color' => $this->urgency_color,
            'deadline' => $this->deadline,
            'is_overdue' => $this->is_overdue,
            'remarks' => $this->remarks,
            'document_type' => [
                'id' => $this->documentType?->id,
                'name' => $this->documentType?->name,
                'code' => $this->documentType?->code,
            ],
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->full_name,
                'email' => $this->creator?->email,
            ],
            'current_holder' => [
                'id' => $this->currentHolder?->id,
                'name' => $this->currentHolder?->full_name,
                'email' => $this->currentHolder?->email,
            ],
            'origin_department' => [
                'id' => $this->originDepartment?->id,
                'name' => $this->originDepartment?->name,
                'code' => $this->originDepartment?->code,
            ],
            'completed_at' => $this->completed_at,
            'archived_at' => $this->archived_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
