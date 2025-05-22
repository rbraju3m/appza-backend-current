{{--
@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h6>{{__('messages.RequestLog')}}</h6>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table id="leave_settings" class="table table-bordered mainTable text-center" style="min-width: 1200px;">

                            --}}
{{--                            <table id="leave_settings" class="table table-bordered datatable table-responsive mainTable text-center">--}}{{--

                                <thead class="thead-dark">
                                <tr>
                                    <th>{{__('messages.SL')}}</th>
                                    <th>{{__('messages.OrderAt')}}</th>
                                    <th>{{__('messages.FromIp')}}</th>
                                    <th>{{__('messages.Status')}}</th>
                                    <th>{{__('messages.ResponseData')}}</th>
                                    <th>{{__('messages.Method')}}</th>
                                    <th>{{__('messages.RequestUrl')}}</th>
                                    <th>{{__('messages.RequestHeader')}}</th>
                                    <th>{{__('messages.RequestData')}}</th>
                                </tr>
                                </thead>

                                @if(sizeof($requestLogs)>0)
                                    <tbody>
                                        @php
                                            $i=1;
                                            $currentPage = $requestLogs->currentPage();
                                            $perPage = $requestLogs->perPage();
                                            $serial = ($currentPage - 1) * $perPage + 1;
                                            $previousHistoryId = null;
                                            $previousProcessTime = null;

                                            $serial = ($requestLogs->currentPage() - 1) * $requestLogs->perPage() + 1;
                                            $buildOrdersArray = $requestLogs->values();
                                        @endphp

                                        @foreach($buildOrdersArray as $index => $log)
                                            <tr>
                                                <td>
                                                    {{$serial++}}
                                                </td>
                                                <td>{{$log->created_at->format('d-M-Y')}}</td>
                                                <td>{{$log->ip_address}}</td>
                                                <td>{{$log->response_status}}</td>
                                                <td style="text-align: left">
                                                    @if(is_array($log->response_data))
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($log->response_data as $key => $values)
                                                                <li>
                                                                    <b style="font-weight: bold">{{ $key }}:</b>
                                                                    {{ is_array($values) ? implode(', ', $values) : $values }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <em>No valid data</em>
                                                    @endif
                                                </td>
                                                <td>{{$log->method}}</td>
                                                <td>{{$log->url}}</td>
                                                <td style="text-align: left">
                                                    @if(is_array($log->headers))
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($log->headers as $key => $values)
                                                                <li>
                                                                    <b style="font-weight: bold">{{ $key }}:</b>
                                                                    {{ is_array($values) ? implode(', ', $values) : $values }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <em>No valid headers</em>
                                                    @endif
                                                </td>
                                                <td style="text-align: left">
                                                    @if(is_array($log->request_data))
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach($log->request_data as $key => $values)
                                                                <li>
                                                                    <b style="font-weight: bold">{{ $key }}:</b>
                                                                    {{ is_array($values) ? implode(', ', $values) : $values }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <em>No valid data</em>
                                                    @endif
                                                </td>
                                            </tr>
                                            @php $i++; @endphp
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                        </div>
                            @if(isset($requestLogs) && count($requestLogs)>0)
                                <div class=" justify-content-right">
                                    {{ $requestLogs->links('layouts.pagination') }}
                                </div>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer.scripts')
    <script>
        let page = 1;
        let loading = false;

        function loadMoreData(page) {
            loading = true;
            $.ajax({
                url: '?page=' + page,
                type: "get",
                beforeSend: function () {
                    // Optionally show loader
                }
            })
                .done(function (data) {
                    if (data.trim().length == 0) {
                        $(window).off('scroll');
                        return;
                    }
                    $('#leave_settings').append($(data).find("#leave_settings tbody").html());
                    loading = false;
                })
                .fail(function (jqXHR, ajaxOptions, thrownError) {
                    console.log('Server error');
                    loading = false;
                });
        }

        $(window).scroll(function () {
            if (loading) return;

            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                page++;
                loadMoreData(page);
            }
        });
    </script>
@endsection
--}}


@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{ __('messages.RequestLog') }}</h6>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <div style="max-height: 700px; overflow-x: auto; overflow-y: auto;white-space: nowrap;" class="table-container">
                            <table class="table table-bordered text-center" style="min-width: 1500px;">
                                <colgroup>
                                    <col style="width: 50px;">
                                    <col style="width: 120px;">
                                    <col style="width: 100px;">
                                    <col style="width: 80px;">
                                    <col style="width: 400px;">
                                    <col style="width: 100px;">
                                    <col style="width: 300px;">
                                    <col style="width: 300px;">
                                    <col style="width: 300px;">
                                </colgroup>
                                <thead class="thead-dark">
                                <tr>
                                    <th>{{ __('messages.SL') }}</th>
                                    <th>{{ __('messages.Date') }}</th>
                                    <th>{{ __('messages.FromIp') }}</th>
{{--                                    <th>{{ __('messages.Method') }}</th>--}}
                                    <th>{{ __('messages.Status') }}</th>
                                    <th>{{ __('messages.ResponseData') }}</th>
                                    <th>{{ __('messages.RequestHeader') }}</th>
                                    <th>{{ __('messages.RequestData') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $serial = ($requestLogs->currentPage() - 1) * $requestLogs->perPage() + 1;
                                @endphp
                                @foreach ($requestLogs as $log)
                                    <tr>
                                        <td>{{ $serial++ }}</td>
                                        <td>{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                                        <td>{{ $log->ip_address }}</td>
{{--                                        <td>{{ $log->method }}</td>--}}
                                        <td>{{ $log->response_status }}</td>
                                        <td class="text-start">
{{--                                            <b>Url : </b> {{$log->url}}--}}
                                            @if (is_array($log->response_data))
                                                <ul class="list-unstyled mb-0">
                                                    @include('request-log/recursive-array-view', ['data' => $log->response_data])
                                                </ul>
                                            @else
                                                <em>No valid data</em>
                                            @endif
                                        </td>

                                        <td class="text-start">
                                            @if(is_array($log->headers))
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($log->headers as $key => $values)
                                                        <li><strong style="font-weight: bold">{{ $key }}:</strong> {{ is_array($values) ? implode(', ', $values) : $values }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <em>No valid headers</em>
                                            @endif
                                        </td>
                                        <td class="text-start">
                                            @if(is_array($log->request_data))
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($log->request_data as $key => $values)
                                                        <li><strong style="font-weight: bold">{{ $key }}:</strong> {{ is_array($values) ? implode(', ', $values) : $values }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <em>No valid data</em>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- ✅ Pagination outside table scroll --}}
                        @if(isset($requestLogs) && count($requestLogs) > 0)
                            <div class="mt-3 d-flex justify-content-end">
                                {{ $requestLogs->links('layouts.pagination') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer.scripts')
    {{-- Additional scripts if needed --}}
@endsection
