<?php

namespace App\Http\Resources;

use App\Models\Addon;
use App\Models\Setup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FreeTrialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $findAddonDownload = Addon::join('appza_fluent_informations','appza_fluent_informations.id','=','appza_product_addons.product_id')
            ->where('appza_fluent_informations.product_slug', $this->product_slug)
            ->where('appza_product_addons.addon_slug', $this->plugin_slug)
            ->select('appza_product_addons.addon_json_info')
            ->lockForUpdate()
            ->first();

        $data = [
            'status'  => 200,
            'url'     => $request->getUri(),
            'method'  => $request->getMethod(),
            'message' => 'Created Successfully',
            'data'    => $findAddonDownload ? json_decode($findAddonDownload->addon_json_info) : null,
        ];

        return $data;

    }
}
