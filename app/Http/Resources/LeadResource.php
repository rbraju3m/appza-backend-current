<?php

namespace App\Http\Resources;

use App\Models\Setup;
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

        $hashKey = match ($this->plugin_name) {
            'appza'        => 'appza_hash',
            'fcom_mobile'  => 'fcom_mobile_hash',
            default        => 'lazy_task_hash',
        };

        $setups = Setup::where('is_active', 1)->get()->toArray();
//        dump($setups);
// Prepare dynamic setup data
        $setupData = [];
        foreach ($setups as $setup) {
            $setupData[$setup['key']] = $setup['value'];
        }

        return [
            'status' => 200, // HTTP OK
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'message' => 'Created Successfully',
            'data' => [
                $hashKey => $this->appza_hash,
            ]+ $setupData,
        ];
    }
}
