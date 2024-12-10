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
        if (!$this->authorization) {
            return new JsonResponse([
                'status' => Response::HTTP_UNAUTHORIZED,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/json']);
        }

        $pageSlug = $request->query('page_slug');
        $pluginSlug = $request->query('plugin_slug');

        if (!$pageSlug || !$pluginSlug) {
            return new JsonResponse([
                'status' => Response::HTTP_OK,
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'message' => 'Parameters missing',
            ], Response::HTTP_OK, ['Content-Type' => 'application/json']);
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
                    ])
                    ->join('appfiy_layout_type', 'appfiy_layout_type.id', '=', 'appfiy_component.layout_type_id')
                    ->join('appfiy_component_type', 'appfiy_component_type.id', '=', 'appfiy_component.component_type_id')
                    ->where('appfiy_component.scope', 'like', '%' . $pageSlug . '%')
                    ->where('appfiy_component.plugin_slug',$pluginSlug)
                    ->where('appfiy_component.is_active', 1)
                    ->whereNull('appfiy_component.deleted_at')
                    ->get()->toArray();

                $final = [];
                if (count($getPagesComponents) > 0) {
                    foreach ($getPagesComponents as $pageComponent) {
                        $componentGeneral = [];
                        $pageComponent = (array)$pageComponent;

                        $getActiveStyleForComponent = ComponentStyleGroup::where([['component_id', $pageComponent['id']], ['is_checked', true]])->get();
                        $styleArrayId = [];
                        foreach ($getActiveStyleForComponent as $item) {
                            array_push($styleArrayId, $item['style_group_id']);
                        }

                        $styleGroups = DB::table('appfiy_component_style_group_properties')
                            ->join('appfiy_style_group', 'appfiy_style_group.id', '=', 'appfiy_component_style_group_properties.style_group_id')
                            ->select([
                                'appfiy_component_style_group_properties.id',
                                'appfiy_component_style_group_properties.name',
                                'appfiy_component_style_group_properties.input_type',
                                'appfiy_component_style_group_properties.value',
                                'appfiy_style_group.slug'
                            ])
                            ->where('appfiy_component_style_group_properties.component_id', $pageComponent['id'])
                            ->whereIn('appfiy_component_style_group_properties.style_group_id', $styleArrayId)
                            ->where('appfiy_component_style_group_properties.is_active', 1)
                            ->whereNull('appfiy_component_style_group_properties.deleted_at')
                            ->get()->toArray();

                        $newStyle = [];
                        foreach ($styleGroups as $sty) {
                            $sty = (array)$sty;
                            if ('list_view_decoration' == $sty['slug']) {
                                $newStyle['general_decoration'][$sty['name']] = $sty['value'];
                            } else {
                                $newStyle[$sty['slug']][$sty['name']] = $sty['value'];
                            }
                        }

                        $componentGeneral['page_id'] = null;
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
                        $componentGeneral['properties'] = $this->addComponentProperties($pageComponent, $pageSlug,$pluginSlug);
                        $componentGeneral['customize_properties'] = $this->addComponentProperties($pageComponent, $pageSlug,$pluginSlug);

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
                    'status' => Response::HTTP_OK,
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'message' => 'Data Not Found',
                ], Response::HTTP_OK, ['Content-Type' => 'application/json']);
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
            'class_type' => $pluginPrefix.$pageComponent['product_type'],
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
                "category" => "shorts",
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ],
            [
                "name" => "Banner 2",
                "image" => $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null,
                "category" => "performance-fabrics",
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ],
            [
                "name" => "Banner 3",
                "image" => $pageComponent['image'] ? config('app.image_public_path') . $pageComponent['image'] : null,
                "category" => "men-saleclothing",
                "button_text" => "Sale2",
                "button_color" => "#ffffff",
            ],
        ];
    }


}
