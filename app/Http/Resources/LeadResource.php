<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        // Determine the hash key based on plugin
        $hashKey = $this->plugin_name == 'appza' ? 'appza_hash' : 'lazy_task_hash';

        return [
            'status' => 200, // HTTP OK
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'message' => 'Created Successfully',
            'data' => [
                $hashKey => $this->appza_hash,
            ],
        ];
    }
}
