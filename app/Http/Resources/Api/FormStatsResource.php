<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_type_id' => $this->form_type_id,
            'form_type_name' => $this->formType?->name,
            'date_submitted' => $this->date_submitted,
            'total' => $this->total,
            'date' => $this->date,
        ];
    }
}
