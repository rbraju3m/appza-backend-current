<div class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-logo"><img src="{{ asset('Appza.png') }}" width="75%" alt=""></a>
    </div>
    <!-- sidebar-header -->
    <div class="sidebar-search">
        <div class="search-body">
            <i data-feather="home"></i>
            <a href="" style="color: #000000b3">Dashboard</a>
            {{--
            <input type="text" class="form-control" placeholder="Search...">--}}
        </div><!-- search-body -->
    </div><!-- sidebar-search -->
    <div class="sidebar-body pt-20">

        @if(auth()->user()->user_type === 'DEVELOPER' || auth()->user()->user_type === 'ADMIN')

        <div class="nav-group {{ Request::is('appza/layout-type/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.layoutType')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('layout_type_list')}}" class="nav-link {{ Request::is('appza/layout-type/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.layoutType')}}</span></a>
                </li>
            </ul>
        </div>

        <div class="nav-group {{ Request::is('appza/style-group/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.style-group')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('style_group_list')}}" class="nav-link {{ Request::is('appza/style-group/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.styleGroupList')}}</span></a>
                    <a href="{{route('style_group_create')}}" class="nav-link {{ Request::is('appza/style-group/create') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>Create Style Group</span></a>
                </li>
            </ul>
        </div>

        <div class="nav-group {{ Request::is('appza/page/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.pages')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('page_list')}}" class="nav-link {{ Request::is('appza/page/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.pageList')}}</span></a>
                    <a href="{{route('scope_list')}}" class="nav-link {{ Request::is('appza/page/scope/*') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.scopeList')}}</span></a>
                </li>
            </ul>
        </div>


        <div class="nav-group {{ Request::is('appza/component-group/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.componentGroup')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('component_group_list')}}" class="nav-link {{ Request::is('appza/component-group/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.componentGroupList')}}</span></a>
                    <a href="{{route('component_group_add')}}" class="nav-link {{ Request::is('appza/component-group/create') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.createComponentType')}}</span></a>
                </li>
            </ul>
        </div>


        <div class="nav-group {{ Request::is('appza/component/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.component')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('component_list')}}" class="nav-link {{ Request::is('appza/component/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.componentList')}}</span></a>
                    <a href="{{route('component_add')}}" class="nav-link {{ Request::is('appza/component/create') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.createComponent')}}</span></a>
                </li>
            </ul>
        </div>


        <div class="nav-group {{ Request::is('appza/global-config/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.GlobalConfig')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('global_config_list')}}" class="nav-link {{ Request::is('appza/global-config/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.GlobalConfigList')}}</span></a>
                </li>
            </ul>
        </div>

        <div class="nav-group {{ Request::is('appza/theme/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.Theme')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('theme_list')}}" class="nav-link {{ Request::is('appza/theme/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.themeList')}}</span></a>

                    <a href="{{route('theme_sort')}}" class="nav-link {{ Request::is('appza/theme/sort') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.themeSort')}}</span></a>
                </li>
            </ul>
        </div>


        <div class="nav-group {{ Request::is('appza/plugin/*') ? 'show' : ''}}">
            <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.Plugin')}}</div>
            <ul class="nav-sidebar">
                <li class="nav-item ">
                    <a href="{{route('plugin_list')}}" class="nav-link {{ Request::is('appza/plugin/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.pluginList')}}</span></a>
                    <a href="{{route('plugin_sort')}}" class="nav-link {{ Request::is('appza/plugin/sort') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.themeSort')}}</span></a>

                </li>
            </ul>
        </div>


        @endif

        @if(auth()->user()->user_type === 'DEVELOPER' || auth()->user()->user_type === 'ADMIN' || auth()->user()->user_type === 'ANDROID')
            <div class="nav-group {{ Request::is('appza/build-order/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.BuildOrder')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('build_order_list')}}" class="nav-link {{ Request::is('appza/build-order/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.BuildOrderList')}}</span></a>
                    </li>
                </ul>
            </div>
        @endif



        @if(auth()->user()->user_type === 'DEVELOPER' || auth()->user()->user_type === 'ADMIN' || auth()->user()->user_type === 'PLUGIN')
            <div class="nav-group {{ Request::is('appza/addon-version/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.AddonVersion')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('addon_version_list')}}" class="nav-link {{ Request::is('appza/addon-version/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.AddonVersion')}}</span></a>
                    </li>
                </ul>
            </div>
        @endif

        @if(auth()->user()->user_type === 'DEVELOPER' || auth()->user()->user_type === 'ADMIN')
            <div class="nav-group {{ Request::is('appza/license/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;">{{__('messages.License')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('license_logic_list')}}" class="nav-link {{ Request::is('appza/license/logic/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.Matrix')}}</span></a>
                        <a href="{{route('license_message_list')}}" class="nav-link {{ Request::is('appza/license/message/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.Message')}}</span></a>
                    </li>
                </ul>
            </div>
        @endif

        @if(auth()->user()->user_type === 'DEVELOPER')
            <div class="nav-group {{ Request::is('appza/setup/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;color: red">{{__('messages.Setup')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('setup_list')}}" class="nav-link {{ Request::is('appza/setup/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.SetupList')}}</span></a>
                    </li>
                </ul>
            </div>
            <div class="nav-group {{ Request::is('appza/request-log/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;color: red">{{__('messages.RequestLog')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('request_log_list')}}" class="nav-link {{ Request::is('appza/request-log/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.RequestLogList')}}</span></a>
                    </li>
                </ul>
            </div>
            <div class="nav-group {{ Request::is('appza/lead/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;color: red">{{__('messages.Lead')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('lead_list')}}" class="nav-link {{ Request::is('appza/lead/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.list')}}</span></a>
                    </li>
                </ul>
            </div>
            <div class="nav-group {{ Request::is('appza/product/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;color: red">{{__('messages.Product')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('product_list')}}" class="nav-link {{ Request::is('appza/product/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.list')}}</span></a>
                    </li>
                </ul>
            </div>
            <div class="nav-group {{ Request::is('appza/free-trial/*') ? 'show' : ''}}">
                <div class="nav-group-label" style="font-size: 15px !important;color: red">{{__('messages.FreeTrial')}}</div>
                <ul class="nav-sidebar">
                    <li class="nav-item ">
                        <a href="{{route('free_trial_list')}}" class="nav-link {{ Request::is('appza/free-trial/list') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.list')}}</span></a>
                    </li>
                </ul>
            </div>

                <div class="nav-group {{ Request::is('appza/report/*') ? 'show' : ''}}">
                    <div class="nav-group-label" style="font-size: 15px !important;color: red">{{__('messages.Reports')}}</div>
                    <ul class="nav-sidebar">
                        <li class="nav-item ">
                            <a href="{{route('report_total_overview')}}" class="nav-link {{ Request::is('appza/report/total-overview') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.TotalOverview')}}</span></a>
                            <a href="{{route('report_total_overview_table')}}" class="nav-link {{ Request::is('appza/report/total-overview-table') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.TotalOverviewTable')}}</span></a>
                            <a href="{{route('report_lead_wise_details')}}" class="nav-link {{ Request::is('appza/report/lead-wise-details') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.leadWiseDetails')}}</span></a>
                            <a href="{{route('report_lead_wise_graph')}}" class="nav-link {{ Request::is('appza/report/lead-wise') ? 'active' : ''}}"><i data-feather="arrow-right"></i><span>{{__('messages.leadWisGraph')}}</span></a>
                        </li>
                    </ul>
                </div>

        @endif


    </div><!-- sidebar-body -->


    <div class="sidebar-footer">
        <a href="" class="avatar online"><span class="avatar-initial" style="font-size: 15px;">
                <img src="{{asset('Fav.svg')}}" alt="">
            </span></a>
        <div class="avatar-body">
            <div class="d-flex align-items-center justify-content-between">
                <h6>{{auth()->user()?auth()->user()->name:''}}</h6>
                <a href="" class="footer-menu"><i class="ri-settings-4-line"></i></a>
            </div>
            <span>
                {{--@php
                    $roles = auth()->user()->getRoleNames();
                    foreach ($roles as $role){
                        if (!empty($role)){
                            echo $role.' <br> ';
                        }
                    }
                @endphp--}}
            </span>
        </div><!-- avatar-body -->
    </div><!-- sidebar-footer -->
</div>

