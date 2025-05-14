<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ComponentStyleGroup;
use App\Models\GlobalConfig;
use App\Models\GlobalConfigComponent;
use App\Models\Lead;
use App\Models\SupportsPlugin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class GlobalConfigController extends Controller
{
    protected $authorization;
    protected $domain;
    protected $pluginName;

    public function __construct(Request $request){
        $data = Lead::checkAuthorization($request);
        $this->authorization = $data['auth_type'] ?? false;
        $this->domain = $data['domain'] ?? '';
        $this->pluginName = $data['plugin_name'] ?? '';
    }



    public function index(Request $request)
    {
        $isHashAuthorization = config('app.is_hash_authorization');
        if ($isHashAuthorization && !$this->authorization) {
            return $this->buildJsonResponse(
                Response::HTTP_UNAUTHORIZED,
                $request,
                'Unauthorized'
            );
        }

        $mode = $request->query('mode');

        $pluginSlug = $request->query('plugin_slug');

        if (!$pluginSlug || !is_array($pluginSlug)) {
            return $this->buildJsonResponse(
                Response::HTTP_NOT_FOUND,
                $request,
                'Plugin slug must be an array and cannot be empty'
            );
        }

        if (!$mode) {
            return $this->buildJsonResponse(
                Response::HTTP_NOT_FOUND,
                $request,
                'Mode cannot be empty'
            );
        }

        try {
            $globalConfigs = $this->getGlobalConfigs($mode,$pluginSlug);

            if ($globalConfigs->isEmpty()) {
                return $this->buildJsonResponse(
                    Response::HTTP_NOT_FOUND,
                    $request,
                    'Data Not Found'
                );
            }

            $data = $globalConfigs->map(function ($global) {
                $generalData = $this->generateGeneralData($global);
                $generalData['components'] = $this->getComponentsData($global['id'],$global['plugin_slug']);
                return $generalData;
            });

            return $this->buildJsonResponse(
                Response::HTTP_OK,
                $request,
                'Data Found',
                $data
            );
        } catch (Exception $ex) {
            return response(['message' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getGlobalConfigs($mode,$pluginSlug)
    {
        return GlobalConfig::where('appfiy_global_config.mode', $mode)
            ->whereIn('appfiy_global_config.plugin_slug', $pluginSlug)
            ->where('appfiy_global_config.is_active', 1)
            ->leftJoin('currency', 'currency.id', '=', 'appfiy_global_config.currency_id')
            ->select([
                'appfiy_global_config.*',
                'currency.country as country_name',
                'currency.currency as currency_name',
                'currency.code as currency_code',
                'currency.symbol as currency_symbol',
            ])
            ->whereNull('appfiy_global_config.deleted_at')
            ->get();
    }

    private function generateGeneralData($global)
    {
        $generalData = [
            'unique_id' => substr(md5(mt_rand()), 0, 10),
            'id' => null,
            'mode' => $global['mode'],
            'name' => $global['name'],
            'slug' => $global['slug'],
            'plugin_slug' => $global['plugin_slug'],
            'image_url' => $global['image'] ? config('app.image_public_path') . $global['image'] : null,
            'is_active' => $global['is_active'] == 1 ? "yes" : "no",
            'is_upcoming' => $global['is_upcoming'] == 1,
            'properties' => $this->generateProperties($global),
            'customize_properties' => [],
            'styles' => [],
            'customize_styles' => [],
        ];

        $generalData['customize_properties'] = $generalData['properties'];
        $generalData['styles'] = $generalData['properties'];
        $generalData['customize_styles'] = $generalData['properties'];

        return $generalData;
    }

    private function generateProperties($global)
    {
        return [
            'selected_color' => $global['selected_color'],
            'unselected_color' => $global['unselected_color'],
            'background_color' => $global['background_color'],
            'layout' => $global['layout'],
            'icon_theme_size' => $global['icon_theme_size'],
            'icon_theme_color' => $global['icon_theme_color'],
            'shadow' => $global['shadow'],
            'icon' => $global['icon'],
            'automatically_imply_leading' => $global['automatically_imply_leading'],
            'center_title' => $global['center_title'],
            'flexible_space' => $global['flexible_space'],
            'bottom' => $global['bottom'],
            'shape_type' => $global['shape_type'],
            'toolbar_opacity' => $global['toolbar_opacity'],
            'actions_icon_theme_color' => $global['actions_icon_theme_color'],
            'actions_icon_theme_size' => $global['actions_icon_theme_size'],
            'title_spacing' => $global['title_spacing'],
            'general_properties' => [
                'margin_x' => $global['margin_x'],
                'margin_y' => $global['margin_y'],
                'padding_x' => $global['padding_x'],
                'padding_y' => $global['padding_y'],
                'shadow' => $global['shadow'],
                'border_color' => $global['background_color'],
                'border_width' => "0",
                'shape_radius' => $global['shape_border_radius'],
                'background_color' => $global['background_color'],
                'float' => $global['float'] == 1,
                'country_name' => $global['country_name'],
                'currency_name' => $global['currency_name'],
                'currency_code' => $global['currency_code'],
                'currency_symbol' => $global['currency_symbol'],
            ],
            'text_properties' => [
                'color' => $global['text_properties_color'],
                'font_style' => 'normal',
                'font_weight' => "700",
                'text' => null,
                'text_decoration' => 'none',
            ],
            'icon_properties' => [
                'size' => number_format($global['icon_properties_size'], 1),
                'color' => $global['icon_properties_color'],
                'weight' => "2.0",
                'shape_radius' => $global['icon_properties_shape_radius'],
                'background_color' => $global['icon_properties_background_color'],
                'is_transparent_background' => $global['is_transparent_background'] == 1,
                'selected_color' => $global['selected_color'],
                'unselected_color' => $global['unselected_color'],
                'margin_x' => number_format($global['icon_properties_margin_x'], 1),
                'margin_y' => number_format($global['icon_properties_margin_y'], 1),
                'padding_x' => number_format($global['icon_properties_padding_x'], 1),
                'padding_y' => number_format($global['icon_properties_padding_y'], 1),
            ],
            'image_properties' => [
                'height' => number_format($global['image_properties_height'], 1),
                'width' => number_format($global['image_properties_width'], 1),
                'shape_radius' => number_format($global['image_properties_shape_radius'], 1),
                'margin_x' => number_format($global['image_properties_margin_x'], 1),
                'margin_y' => number_format($global['image_properties_margin_y'], 1),
                'padding_x' => number_format($global['image_properties_padding_x'], 1),
                'padding_y' => number_format($global['image_properties_padding_y'], 1),
            ],
        ];
    }

    private function getComponentsData($globalConfigId,$pluginSlug)
    {
        $components = GlobalConfigComponent::where('appfiy_global_config_component.global_config_id', $globalConfigId)
            ->where('appfiy_component.plugin_slug', $pluginSlug)
            ->whereNull('appfiy_component.deleted_at')
            ->select([
                'appfiy_component.*',
                'appfiy_component_type.name as group_name',
                'appfiy_layout_type.slug as layout_type',
                'appfiy_global_config_component.component_position',
            ])
            ->join('appfiy_component', 'appfiy_component.id', '=', 'appfiy_global_config_component.component_id')
            ->join('appfiy_component_type', 'appfiy_component_type.id', '=', 'appfiy_component.component_type_id')
            ->join('appfiy_layout_type', 'appfiy_layout_type.id', '=', 'appfiy_component.layout_type_id')
            ->orderBy('appfiy_global_config_component.component_position', 'asc')
            ->get();

        return $components->map(function ($component) use ($pluginSlug) {
            return [
                'component_position' => $component['component_position'],
                'name' => $component['name'],
                'slug' => $component['slug'],
                'support_extension' => $component['plugin_slug'],
                'is_upcoming' => $component['is_upcoming'] == 1,
                'image_url' => $component['image'] ? config('app.image_public_path') . $component['image'] : null,
                'is_active' => $component['is_active'] == 1,
                'properties' => $this->generateComponentProperties($component,$pluginSlug),
                'customize_properties' => $this->generateComponentProperties($component,$pluginSlug),
                'styles' => $this->generateComponentStyles($component['id']),
                'customize_styles' => $this->generateComponentStyles($component['id']),
            ];
        })->toArray();
    }

    private function generateComponentProperties($component,$pluginSlug)
    {
        $getPluginPrefix = SupportsPlugin::getPluginPrefix($pluginSlug);
        return [
            'label' => $component['label'],
            'group_name' => $component['group_name'],
            'layout_type' => $component['layout_type'],
            'icon_code' => $component['icon_code'],
            'event' => $component['event'],
            'scope' => json_decode($component['scope']),
            'class_type' => $component['product_type']?$getPluginPrefix.$component['product_type']:null,
            'web_icon' => $component['web_icon'],
            'is_multiple' => $component['is_multiple'],
            'selected_design' => $component['selected_design'],
            'detailsPage' => $component['details_page'],
            'is_transparent_background' => $component['transparent'] === 'True',
        ];
    }

    private function generateComponentStyles($componentId)
    {
        $styleGroups = ComponentStyleGroup::where('component_id', $componentId)
            ->where('is_checked', true)
            ->get()
            ->pluck('style_group_id');

        $styles = DB::table('appfiy_component_style_group_properties')
            ->join('appfiy_style_group', 'appfiy_style_group.id', '=', 'appfiy_component_style_group_properties.style_group_id')
            ->select([
                'appfiy_component_style_group_properties.name',
                'appfiy_component_style_group_properties.input_type',
                'appfiy_component_style_group_properties.value',
                'appfiy_style_group.slug',
            ])
            ->where('appfiy_component_style_group_properties.component_id', $componentId)
            ->whereIn('appfiy_component_style_group_properties.style_group_id', $styleGroups)
            ->where('appfiy_component_style_group_properties.is_active', 1)
            ->whereNull('appfiy_component_style_group_properties.deleted_at')
            ->get();

        $stylesGrouped = [];
        foreach ($styles as $style) {
            $stylesGrouped[$style->slug][$style->name] = $style->value;
        }

        return $stylesGrouped;
    }

    private function buildJsonResponse($status, $request, $message, $data = null)
    {
        $response = [
            'status' => $status,
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

}
