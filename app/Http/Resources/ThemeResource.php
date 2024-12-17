<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeResource extends JsonResource
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
            'plugin_slug' => $this->plugin_slug,
            'created' => $this->created_at ? $this->created_at->format('d-M-Y') : null,
            'background_color' => $this->background_color,
            'font_family' => $this->font_family,
            'text_color' => $this->text_color,
            'font_size' => $this->font_size,
            'is_transparent_background' => $this->transparent === 'True',
            'dashboard_page' => $this->dashboard_page,
            'login_page' => $this->login_page,
            'login_modal' => $this->login_modal,
            'image_url' => $this->image
                ? config('app.image_public_path') . $this->image
                : null,
            'pages_preview' => $this->photoGallery->map(function ($photoGallery) {
                return $photoGallery->image
                    ? config('app.image_public_path') . $photoGallery->image
                    : null;
            })->filter()->toArray(), // Map and remove null image URLs
            'default_active_page_slug' => $this->default_page,
        ];
    }
}
