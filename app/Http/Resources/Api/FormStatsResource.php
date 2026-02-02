<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'filters' => $this->resource['filters'],
            'stats' => $this->resource['stats'],
            'forms_by_type' => $this->resource['forms_by_type'],
            'daily_submissions' => $this->resource['daily_submissions'],
        ];
    }
}
