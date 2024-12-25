<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageComponentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $newStyle = [];
        foreach ($this->styleGroups as $styleGroup) {
            $newStyle[$styleGroup->style_group_slug][$styleGroup->name] = $styleGroup->value;
        }

        return [
            'page_id' => null,
            'unique_id' => substr(md5(mt_rand()), 0, 10),
            'name' => $this->name,
            'slug' => $this->slug,
            'is_upcoming' => (bool) $this->is_upcoming,
            'mode' => 'component',
            'corresponding_page_slug' => $request->query('page_slug'),
            'component_image' => $this->image ? config('app.image_public_path') . $this->image : null,
            'image_url' => $this->image_url ? config('app.image_public_path') . $this->image_url : null,
            'is_active' => (bool) $this->is_active,
            'properties' => [
                'label' => $this->label,
                'group_name' => $this->component_group,
                'layout_type' => $this->layout_type,
                'icon_code' => $this->icon_code,
                'event' => $this->event,
                'scope' => json_decode($this->scope),
                'class_type' => $this->product_type,
                'is_multiple' => $this->is_multiple,
                'selected_design' => $this->selected_design,
                'detailsPage' => $this->details_page,
                'is_transparent_background' => $this->transparent === 'True',
                'items' => null, // Initialize if needed
            ],
            'styles' => $newStyle,
            'customize_properties' => [
                'label' => $this->label,
                'group_name' => $this->component_group,
                'layout_type' => $this->layout_type,
                'icon_code' => $this->icon_code,
                'event' => $this->event,
                'scope' => json_decode($this->scope),
                'selected_design' => $this->selected_design,
                'detailsPage' => $this->details_page,
                'is_transparent_background' => $this->transparent === 'True',
            ],
            'customize_styles' => $newStyle,
        ];
    }
}
