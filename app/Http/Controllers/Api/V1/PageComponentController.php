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
use Illuminate\Support\Facades\Log;
use stdClass;
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

    public function index(Request $request)
    {
        $isHashAuthorization = config('app.is_hash_authorization');

        if ($isHashAuthorization && !$this->authorization) {
            return new JsonResponse([
                'status' => Response::HTTP_UNAUTHORIZED,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $pageSlug = $request->query('page_slug');
        $pluginSlug = $request->query('plugin_slug');

        if (!is_array($pluginSlug) || empty($pluginSlug)) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Plugin slug must be a non-empty array',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$pageSlug) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Page slug cannot be empty',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $getPage = Page::where('slug', $pageSlug)
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->first();

            if (!$getPage) {
                return new JsonResponse([
                    'status' => Response::HTTP_NOT_FOUND,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Page Not Found',
                ], Response::HTTP_NOT_FOUND);
            }

            $getPagesComponents = DB::table('appfiy_component')
                ->select([
                    'appfiy_component.id',
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
                    'appfiy_component.image_url',
                    'appfiy_component.is_multiple',
                    'appfiy_component.selected_design',
                    'appfiy_component.details_page',
                    'appfiy_component.transparent',
                    'appfiy_component_type.name as component_group',
                    'appfiy_component_type.slug as component_group_slug',
                    'appfiy_component_type.icon as component_group_icon',
                    'appfiy_component.deleted_at',
                    'appfiy_component.plugin_slug',
                    'appfiy_component.items',
                    'appfiy_component.dev_data',
                    'appfiy_component.filters',
                    'appfiy_component.pagination',
                    'appfiy_component.show_no_data_view'
                ])
                ->join('appfiy_layout_type', 'appfiy_layout_type.id', '=', 'appfiy_component.layout_type_id')
                ->join('appfiy_component_type', 'appfiy_component_type.id', '=', 'appfiy_component.component_type_id')
                ->whereRaw('JSON_CONTAINS(appfiy_component.scope, ?)', ['"' . $pageSlug . '"'])
                ->whereIn('appfiy_component.plugin_slug', $pluginSlug)
                ->where('appfiy_component.is_active', 1)
                ->whereNull('appfiy_component.deleted_at')
                ->get();

            $componentIds = $getPagesComponents->pluck('id')->toArray();

            $allStyleGroups = ComponentStyleGroup::whereIn('component_id', $componentIds)
                ->where('is_checked', true)
                ->get()
                ->groupBy('component_id');

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

            foreach ($getPagesComponents as $pageComponent) {
                $componentGeneral = [];
                $pageComponent = (array)$pageComponent;

                $componentStyleGroups = $allStyleGroups->get($pageComponent['id'], collect());
                $styleArrayId = $componentStyleGroups->pluck('style_group_id')->toArray();

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
                $componentGeneral['is_upcoming'] = $pageComponent['is_upcoming'] == 1;
                $componentGeneral['mode'] = 'component';
                $componentGeneral['corresponding_page_slug'] = $pageSlug;
                $componentGeneral['component_image'] = $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null;
                $componentGeneral['image_url'] = $pageComponent['image_url'] ? config('app.image_public_path') . $pageComponent['image_url'] : null;
                $componentGeneral['is_active'] = $pageComponent['is_active'] == 1;

                $componentGeneral['properties'] = $this->addComponentProperties($pageComponent, $pageSlug, $pageComponent['plugin_slug']);

                $componentGeneral['customize_properties'] = $componentGeneral['properties'];

                if ($pageComponent['layout_type'] == 'ListViewVertical') {

                    /*IF EMPTY OBJECT NOT REPLACE TO ARRAY & SAME OBJECT SEND API FROM DB BY REQ BY SAIFUL START*/
                    foreach (['items', 'dev_data', 'filters', 'pagination'] as $key) {
                        if (empty($pageComponent[$key])) {
                            continue;
                        }

                        $decoded = $pageComponent[$key];

                        if (is_string($decoded)) {
                            $first = json_decode($decoded, false); // decode as object

                            if (is_string($first)) {
                                $second = json_decode($first, false);
                                $decoded = json_last_error() === JSON_ERROR_NONE ? $second : $first;
                            } elseif (is_object($first) || is_array($first)) {
                                $decoded = $first;
                            } else {
                                $decoded = null;
                            }
                        }

                        if ($key === 'dev_data' && is_object($decoded)) {
                            foreach ((array) $decoded as $devKey => $devValue) {
                                $componentGeneral['properties'][$devKey] = $devValue;
                                $componentGeneral['customize_properties'][$devKey] = $devValue;
                            }
                        } else {
                            $componentGeneral['properties'][$key] = $decoded;
                            $componentGeneral['customize_properties'][$key] = $decoded;
                        }
                    }
                    /*IF EMPTY OBJECT NOT REPLACE TO ARRAY & SAME OBJECT SEND API FROM DB BY REQ BY SAIFUL END*/


                    /*IF EMPTY OBJECT REPLACE TO NULL BY REQ BY SOHEL VI START*/
                    /*$replaceEmptyObjectsWithNull = function (mixed $data) use (&$replaceEmptyObjectsWithNull): mixed {
                        if ($data instanceof stdClass) {
                            $vars = get_object_vars($data);

                            if (empty($vars)) {
                                return null;
                            }

                            foreach ($vars as $key => $value) {
                                $data->$key = $replaceEmptyObjectsWithNull($value);
                            }

                            return $data;
                        }

                        if (is_array($data)) {
                            foreach ($data as $key => $value) {
                                $data[$key] = $replaceEmptyObjectsWithNull($value);
                            }

                            return $data;
                        }

                        return $data;
                    };
                    // Your loop starts here
                    foreach (['items', 'dev_data', 'filters', 'pagination'] as $key) {
                        if (empty($pageComponent[$key])) {
                            continue;
                        }

                        $decoded = $pageComponent[$key];

                        if (is_string($decoded)) {
                            $first = json_decode($decoded, false); // decode as object

                            if (is_string($first)) {
                                $second = json_decode($first, false);
                                $decoded = json_last_error() === JSON_ERROR_NONE ? $second : $first;
                            } elseif (is_object($first) || is_array($first)) {
                                $decoded = $first;
                            } else {
                                $decoded = null;
                            }
                        }

                        // 🔁 Apply the transformer
                        $decoded = $replaceEmptyObjectsWithNull($decoded);

                        if ($key === 'dev_data' && is_object($decoded)) {
                            foreach ((array) $decoded as $devKey => $devValue) {
                                $componentGeneral['properties'][$devKey] = $devValue;
                                $componentGeneral['customize_properties'][$devKey] = $devValue;
                            }
                        } else {
                            $componentGeneral['properties'][$key] = $decoded;
                            $componentGeneral['customize_properties'][$key] = $decoded;
                        }
                    }*/
                    /*IF EMPTY OBJECT REPLACE TO NULL BY REQ BY SOHEL VI END*/

                }

                if ($pageComponent['layout_type'] === 'BannerSliderHorizontal') {
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

            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Data Found',
                'data' => array_values($final)
            ], Response::HTTP_OK, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);

        } catch (Exception $ex) {
            return response()->json([
                'message' => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Recursively replace empty stdClass objects with null.
     *
     * @param mixed $data
     * @return mixed
     */
    function replaceEmptyObjectsWithNull(mixed $data): mixed
    {
        if ($data instanceof stdClass) {
            $vars = get_object_vars($data);

            if (empty($vars)) {
                return null;
            }

            foreach ($vars as $key => $value) {
                $data->$key = replaceEmptyObjectsWithNull($value);
            }

            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = replaceEmptyObjectsWithNull($value);
            }

            return $data;
        }

        return $data;
    }


    private function addComponentProperties($pageComponent, $pageSlug, $pluginSlug)
    {
        $pluginPrefix = SupportsPlugin::getPluginPrefix($pluginSlug);
        $properties = [
            'label' => $pageComponent['label'],
            'group_name' => $pageComponent['component_group'],
            'layout_type' => $pageComponent['layout_type'],
            'icon_code' => $pageComponent['icon_code'],
            'event' => $pageComponent['event'],
            'scope' => json_decode($pageComponent['scope'], true),
            'class_type' => $pageComponent['product_type'] ? $pluginPrefix . $pageComponent['product_type'] : null,
            'is_multiple' => $pageComponent['is_multiple'],
            'selected_design' => $pageComponent['selected_design'],
            'selected_category' => null,
            'selected_category_slug' => null,
            'selected_category_ids' => null,
            'items' => null,
            'detailsPage' => $pageComponent['details_page'],
            'is_transparent_background' => strtolower($pageComponent['transparent']) === 'true',
        ];

        if ($pageComponent['product_type'] === 'Category') {
            $properties['category_slugs'] = [];
            $properties['all_category'] = true;
        }

        if ($pageComponent['product_type'] === 'CategoryProduct') {
            $properties['categories'] = [];
        }

        if ($pageComponent['layout_type'] == 'ListViewVertical' || $pageComponent['layout_type'] == 'ListViewHorizontal' || $pageComponent['layout_type'] == 'ListViewGrid') {
            $properties['show_no_data_view'] = $pageComponent['show_no_data_view']== 1 ? true : false;
        }

        return $properties;
    }

    private function getBannerSliderItems($pageComponent)
    {
        $image = $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null;

        return [
            [
                "name" => "Banner 1",
                "image" => $image,
                "category" => null,
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ],
            [
                "name" => "Banner 2",
                "image" => $image,
                "category" => null,
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ]
        ];
    }

}
