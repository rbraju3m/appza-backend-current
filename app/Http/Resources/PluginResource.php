<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PluginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'prefix' => $this->prefix,
            'title' => $this->title,
            'description' => $this->description,
            'others' => $this->others,
            'created' => $this->created_at ? $this->created_at->format('d-M-Y') : null,
            'is_disable' => $this->is_disable?true:false,
            'image' => $this->image
                ? config('app.image_public_path') . $this->image
                : null,
        ];
    }
}
