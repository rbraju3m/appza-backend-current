<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ComponentStyleGroup;
use App\Models\Lead;
use App\Models\Page;
use App\Models\SupportsPlugin;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class PageComponentController extends Controller
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

    public function index(Request $request) {
        $isHashAuthorization = config('app.is_hash_authorization');
        if ($isHashAuthorization && !$this->authorization) {
            return new JsonResponse([
                'status' => Response::HTTP_UNAUTHORIZED,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/json']);
        }

        $pageSlug = $request->query('page_slug');
        $pluginSlug = $request->query('plugin_slug');

        if (!$pluginSlug || !is_array($pluginSlug)) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Plugin slug must be an array and cannot be empty',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$pageSlug) {
            return new JsonResponse([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Page slug cannot be empty',
            ], Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }

        try {
            $getPage = Page::where('slug', $pageSlug)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->first();

            if ($getPage) {
                $getPagesComponents = DB::table('appfiy_component')
                    ->select([
                        'appfiy_component.id as id',
                        'appfiy_component.name',
                        'appfiy_component.slug',
                        'appfiy_component.label',
                        'appfiy_component.layout_type_id',
                        'appfiy_layout_type.slug as layout_type',
                        'appfiy_component.icon_code',
                        'appfiy_component.event',
                        'appfiy_component.scope',
                        'appfiy_component.class_type',
                        'appfiy_component.product_type',
                        'appfiy_component.is_active',
                        'appfiy_component.is_upcoming',
                        'appfiy_component.image',
                        'image_url',
                        'appfiy_component.is_multiple',
                        'appfiy_component.selected_design',
                        'appfiy_component.details_page',
                        'appfiy_component.transparent',
                        'appfiy_component_type.name as component_group',
                        'appfiy_component_type.slug as component_group_slug',
                        'appfiy_component_type.icon as component_group_icon',
                        'appfiy_component.deleted_at as deleted_at',
                        'appfiy_component.plugin_slug',
                    ])
                    ->join('appfiy_layout_type', 'appfiy_layout_type.id', '=', 'appfiy_component.layout_type_id')
                    ->join('appfiy_component_type', 'appfiy_component_type.id', '=', 'appfiy_component.component_type_id')
//                    ->where('appfiy_component.scope', 'like', '%' . $pageSlug . '%')
                    ->whereRaw('JSON_CONTAINS(appfiy_component.scope, ?)', ['"' . $pageSlug . '"'])
                    ->whereIn('appfiy_component.plugin_slug', $pluginSlug)
                    ->where('appfiy_component.is_active', 1)
                    ->whereNull('appfiy_component.deleted_at')
                    ->get();

                // FIX: Load all component style groups at once
                $componentIds = $getPagesComponents->pluck('id')->toArray();

                $allStyleGroups = ComponentStyleGroup::whereIn('component_id', $componentIds)
                    ->where('is_checked', true)
                    ->get()
                    ->groupBy('component_id');

                // FIX: Load all style properties at once
                $styleGroupIds = $allStyleGroups->flatten()->pluck('style_group_id')->unique()->toArray();

                $allStyleProperties = DB::table('appfiy_component_style_group_properties')
                    ->join('appfiy_style_group', 'appfiy_style_group.id', '=', 'appfiy_component_style_group_properties.style_group_id')
                    ->select([
                        'appfiy_component_style_group_properties.id',
                        'appfiy_component_style_group_properties.component_id',
                        'appfiy_component_style_group_properties.name',
                        'appfiy_component_style_group_properties.input_type',
                        'appfiy_component_style_group_properties.value',
                        'appfiy_component_style_group_properties.style_group_id',
                        'appfiy_style_group.slug'
                    ])
                    ->whereIn('appfiy_component_style_group_properties.component_id', $componentIds)
                    ->whereIn('appfiy_component_style_group_properties.style_group_id', $styleGroupIds)
                    ->where('appfiy_component_style_group_properties.is_active', 1)
                    ->whereNull('appfiy_component_style_group_properties.deleted_at')
                    ->get()
                    ->groupBy('component_id');

                $final = [];
                if (count($getPagesComponents) > 0) {
                    foreach ($getPagesComponents as $pageComponent) {
                        $componentGeneral = [];
                        $pageComponent = (array)$pageComponent;

                        // FIX: Use pre-loaded data instead of querying
                        $componentStyleGroups = $allStyleGroups->get($pageComponent['id'], collect());
                        $styleArrayId = $componentStyleGroups->pluck('style_group_id')->toArray();

                        // FIX: Use pre-loaded data and filter by style group IDs
                        $componentStyleProperties = $allStyleProperties->get($pageComponent['id'], collect())
                            ->whereIn('style_group_id', $styleArrayId);

                        $newStyle = [];
                        foreach ($componentStyleProperties as $sty) {
                            $sty = (array)$sty;
                            $newStyle[$sty['slug']][$sty['name']] = $sty['value'];
                        }

                        $componentGeneral['page_id'] = null;
                        $componentGeneral['support_extension'] = $pageComponent['plugin_slug'];
                        $componentGeneral['unique_id'] = substr(md5(mt_rand()), 0, 10);
                        $componentGeneral['name'] = $pageComponent['name'];
                        $componentGeneral['slug'] = $pageComponent['slug'];
                        $componentGeneral['is_upcoming'] = $pageComponent['is_upcoming'] === 0 ? false : true;
                        $componentGeneral['mode'] = 'component';
                        $componentGeneral['corresponding_page_slug'] = $pageSlug;
                        $componentGeneral['component_image'] = $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null;
                        $componentGeneral['image_url'] = $pageComponent['image_url'] ? config('app.image_public_path') . $pageComponent['image_url'] : null;
                        $componentGeneral['is_active'] = $pageComponent['is_active'] == 1 ? true : false;

                        // Add Customize Properties
                        $componentGeneral['properties'] = $this->addComponentProperties($pageComponent, $pageSlug, $pageComponent['plugin_slug']);
                        $componentGeneral['customize_properties'] = $this->addComponentProperties($pageComponent, $pageSlug, $pageComponent['plugin_slug']);

                        // Add BannerSliderHorizontal items
                        if ('BannerSliderHorizontal' === $pageComponent['layout_type']) {
                            $items = $this->getBannerSliderItems($pageComponent);
                            $componentGeneral['properties']['items'] = $items;
                            $componentGeneral['customize_properties']['items'] = $items;
                        }

                        $componentGeneral['styles'] = $newStyle;
                        $componentGeneral['customize_styles'] = $newStyle;

                        $final[$pageComponent['component_group_slug']]['name'] = $pageComponent['component_group'];
                        $final[$pageComponent['component_group_slug']]['icon'] = $pageComponent['component_group_icon'];
                        $final[$pageComponent['component_group_slug']]['items'][] = $componentGeneral;
                    }

                    $final = array_values($final);
                }

                return new JsonResponse([
                    'status' => Response::HTTP_OK,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Data Found',
                    'data' => $final
                ], Response::HTTP_OK, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            } else {
                return new JsonResponse([
                    'status' => Response::HTTP_NOT_FOUND,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Page Not Found',
                ], Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
            }
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function addComponentProperties($pageComponent, $pageSlug,$pluginSlug)
    {
        $pluginPrefix = SupportsPlugin::getPluginPrefix($pluginSlug);
        $properties = [
            'label' => $pageComponent['label'],
            'group_name' => $pageComponent['component_group'],
            'layout_type' => $pageComponent['layout_type'],
            'icon_code' => $pageComponent['icon_code'],
            'event' => $pageComponent['event'],
            'scope' => json_decode($pageComponent['scope']),
            'class_type' => $pageComponent['product_type']?$pluginPrefix.$pageComponent['product_type']:null,
            'is_multiple'=>$pageComponent['is_multiple'],
            'selected_design' => $pageComponent['selected_design'],
            'selected_category' => null,
            'selected_category_slug' => null,
            'selected_category_ids' => null,
            'items'=>null,
            'detailsPage' => $pageComponent['details_page'],
            'is_transparent_background' => $pageComponent['transparent'] === 'True' ? true : false,
        ];

        if ($pageComponent['product_type'] == 'Category') {
            $properties['category_slugs'] = [];
            $properties['all_category'] = true;
        }

        if ($pageComponent['product_type'] == 'CategoryProduct') {
            $properties['categories'] = [];
        }

        return $properties;
    }

    private function getBannerSliderItems($pageComponent)
    {
        return [
            [
                "name" => "Banner 1",
                "image" => $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null,
                "category" => null,
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ],
            [
                "name" => "Banner 2",
                "image" => $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null,
                "category" => null,
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ]
        ];
    }
/*    public function index(Request $request) {
        $isHashAuthorization = config('app.is_hash_authorization');
        if ($isHashAuthorization && !$this->authorization) {
            return new JsonResponse([
                'status' => Response::HTTP_UNAUTHORIZED,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/json']);
        }

        $pageSlug = $request->query('page_slug');
        $pluginSlug = $request->query('plugin_slug');

        if (!$pluginSlug || !is_array($pluginSlug)) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Plugin slug must be an array and cannot be empty',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$pageSlug) {
            return new JsonResponse([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Page slug cannot be empty',
            ], Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
        }

        try {
            $getPage = Page::where('slug', $pageSlug)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->first();

            if ($getPage) {
                $getPagesComponents = DB::table('appfiy_component')
                    ->select([
                        'appfiy_component.id as id',
                        'appfiy_component.name',
                        'appfiy_component.slug',
                        'appfiy_component.label',
                        'appfiy_component.layout_type_id',
                        'appfiy_layout_type.slug as layout_type',
                        'appfiy_component.icon_code',
                        'appfiy_component.event',
                        'appfiy_component.scope',
                        'appfiy_component.class_type',
                        'appfiy_component.product_type',
                        'appfiy_component.is_active',
                        'appfiy_component.is_upcoming',
                        'appfiy_component.image',
                        'image_url',
                        'appfiy_component.is_multiple',
                        'appfiy_component.selected_design',
                        'appfiy_component.details_page',
                        'appfiy_component.transparent',
                        'appfiy_component_type.name as component_group',
                        'appfiy_component_type.slug as component_group_slug',
                        'appfiy_component_type.icon as component_group_icon',
                        'appfiy_component.deleted_at as deleted_at',
                        'appfiy_component.plugin_slug',
                    ])
                    ->join('appfiy_layout_type', 'appfiy_layout_type.id', '=', 'appfiy_component.layout_type_id')
                    ->join('appfiy_component_type', 'appfiy_component_type.id', '=', 'appfiy_component.component_type_id')
                    ->where('appfiy_component.scope', 'like', '%' . $pageSlug . '%')
                    ->whereIn('appfiy_component.plugin_slug', $pluginSlug)
                    ->where('appfiy_component.is_active', 1)
                    ->whereNull('appfiy_component.deleted_at')
                    ->get();

                // FIX: Load all component style groups at once
                $componentIds = $getPagesComponents->pluck('id')->toArray();

                $allStyleGroups = ComponentStyleGroup::whereIn('component_id', $componentIds)
                    ->where('is_checked', true)
                    ->get()
                    ->groupBy('component_id');

                // FIX: Load all style properties at once
                $styleGroupIds = $allStyleGroups->flatten()->pluck('style_group_id')->unique()->toArray();

                $allStyleProperties = DB::table('appfiy_component_style_group_properties')
                    ->join('appfiy_style_group', 'appfiy_style_group.id', '=', 'appfiy_component_style_group_properties.style_group_id')
                    ->select([
                        'appfiy_component_style_group_properties.id',
                        'appfiy_component_style_group_properties.component_id',
                        'appfiy_component_style_group_properties.name',
                        'appfiy_component_style_group_properties.input_type',
                        'appfiy_component_style_group_properties.value',
                        'appfiy_component_style_group_properties.style_group_id',
                        'appfiy_style_group.slug'
                    ])
                    ->whereIn('appfiy_component_style_group_properties.component_id', $componentIds)
                    ->whereIn('appfiy_component_style_group_properties.style_group_id', $styleGroupIds)
                    ->where('appfiy_component_style_group_properties.is_active', 1)
                    ->whereNull('appfiy_component_style_group_properties.deleted_at')
                    ->get()
                    ->groupBy('component_id');

                $final = [];
                if (count($getPagesComponents) > 0) {
                    foreach ($getPagesComponents as $pageComponent) {
                        $componentGeneral = [];
                        $pageComponent = (array)$pageComponent;

                        // FIX: Use pre-loaded data instead of querying
                        $componentStyleGroups = $allStyleGroups->get($pageComponent['id'], collect());
                        $styleArrayId = $componentStyleGroups->pluck('style_group_id')->toArray();

                        // FIX: Use pre-loaded data and filter by style group IDs
                        $componentStyleProperties = $allStyleProperties->get($pageComponent['id'], collect())
                            ->whereIn('style_group_id', $styleArrayId);

                        $newStyle = [];
                        foreach ($componentStyleProperties as $sty) {
                            $sty = (array)$sty;
                            $newStyle[$sty['slug']][$sty['name']] = $sty['value'];
                        }

                        $componentGeneral['page_id'] = null;
                        $componentGeneral['support_extension'] = $pageComponent['plugin_slug'];
                        $componentGeneral['unique_id'] = substr(md5(mt_rand()), 0, 10);
                        $componentGeneral['name'] = $pageComponent['name'];
                        $componentGeneral['slug'] = $pageComponent['slug'];
                        $componentGeneral['is_upcoming'] = $pageComponent['is_upcoming'] === 0 ? false : true;
                        $componentGeneral['mode'] = 'component';
                        $componentGeneral['corresponding_page_slug'] = $pageSlug;
                        $componentGeneral['component_image'] = $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null;
                        $componentGeneral['image_url'] = $pageComponent['image_url'] ? config('app.image_public_path') . $pageComponent['image_url'] : null;
                        $componentGeneral['is_active'] = $pageComponent['is_active'] == 1 ? true : false;

                        // Add Customize Properties
                        $componentGeneral['properties'] = $this->addComponentProperties($pageComponent, $pageSlug, $pageComponent['plugin_slug']);
                        $componentGeneral['customize_properties'] = $this->addComponentProperties($pageComponent, $pageSlug, $pageComponent['plugin_slug']);

                        // Add BannerSliderHorizontal items
                        if ('BannerSliderHorizontal' === $pageComponent['layout_type']) {
                            $items = $this->getBannerSliderItems($pageComponent);
                            $componentGeneral['properties']['items'] = $items;
                            $componentGeneral['customize_properties']['items'] = $items;
                        }

                        $componentGeneral['styles'] = $newStyle;
                        $componentGeneral['customize_styles'] = $newStyle;

                        $final[$pageComponent['component_group_slug']]['name'] = $pageComponent['component_group'];
                        $final[$pageComponent['component_group_slug']]['icon'] = $pageComponent['component_group_icon'];
                        $final[$pageComponent['component_group_slug']]['items'][] = $componentGeneral;
                    }

                    $final = array_values($final);
                }

                return new JsonResponse([
                    'status' => Response::HTTP_OK,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Data Found',
                    'data' => $final
                ], Response::HTTP_OK, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
            } else {
                return new JsonResponse([
                    'status' => Response::HTTP_NOT_FOUND,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Page Not Found',
                ], Response::HTTP_NOT_FOUND, ['Content-Type' => 'application/json']);
            }
        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function addComponentProperties($pageComponent, $pageSlug,$pluginSlug)
    {
        $pluginPrefix = SupportsPlugin::getPluginPrefix($pluginSlug);
        $properties = [
            'label' => $pageComponent['label'],
            'group_name' => $pageComponent['component_group'],
            'layout_type' => $pageComponent['layout_type'],
            'icon_code' => $pageComponent['icon_code'],
            'event' => $pageComponent['event'],
            'scope' => json_decode($pageComponent['scope']),
            'class_type' => $pageComponent['product_type']?$pluginPrefix.$pageComponent['product_type']:null,
            'is_multiple'=>$pageComponent['is_multiple'],
            'selected_design' => $pageComponent['selected_design'],
            'selected_category' => null,
            'selected_category_slug' => null,
            'selected_category_ids' => null,
            'items'=>null,
            'detailsPage' => $pageComponent['details_page'],
            'is_transparent_background' => $pageComponent['transparent'] === 'True' ? true : false,
        ];

        if ($pageComponent['product_type'] == 'Category') {
            $properties['category_slugs'] = [];
            $properties['all_category'] = true;
        }

        if ($pageComponent['product_type'] == 'CategoryProduct') {
            $properties['categories'] = [];
        }

        return $properties;
    }

    private function getBannerSliderItems($pageComponent)
    {
        return [
            [
                "name" => "Banner 1",
                "image" => $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null,
                "category" => null,
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ],
            [
                "name" => "Banner 2",
                "image" => $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null,
                "category" => null,
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ]
        ];
    }*/


}
