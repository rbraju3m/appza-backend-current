
@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mb-5">
                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{ __('messages.RequestLog') }}</h6>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <!-- Search Form -->
                        <form method="GET" action="{{ route('request_log_list') }}" id="search-form" class="mb-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="text" name="search" class="form-control" placeholder="{{__('messages.searchPlaceholder')}}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('messages.search')}}
                                    </button>
                                    <a href="{{ route('request_log_list') }}" class="btn btn-secondary">
                                        {{__('messages.clear')}}
                                    </a>
                                </div>
                            </div>
                        </form>

                        {{-- Table Wrapper --}}
                        <div class="table-container">
                            <table class="table table-bordered text-center log-table">
                                <thead class="thead-dark">
                                <tr>
                                    <th style="width: 50px;">{{ __('messages.SL') }}</th>
                                    <th style="width: 250px;">{{ __('messages.Date') }}</th>
                                    <th style="width: 300px;">{{ __('messages.ResponseData') }}</th>
                                    <th style="width: 280px;">{{ __('messages.RequestHeader') }}</th>
                                    <th style="width: 320px;">{{ __('messages.RequestData') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $serial = ($requestLogs->currentPage() - 1) * $requestLogs->perPage() + 1;
                                @endphp
                                @foreach ($requestLogs as $log)
                                    <tr>
                                        <td>{{ $serial++ }}</td>

                                        <td class="text-start meta-info">
                                            <div><strong>Date:</strong> {{ $log->created_at->timezone('Asia/Dhaka')->format('d-M-Y h:i:s A') }}</div>
                                            <div><strong>IP:</strong> {{ $log->ip_address }}</div>
                                            <div><strong>Url:</strong> {{ \Illuminate\Support\Str::before($log->url, '?') }}</div>
                                            <div><strong>Method:</strong> {{ $log->method }}</div>
                                            <div><strong>Status:</strong> {{ $log->response_status }}</div>
                                        </td>


                                        <td class="text-start">
                                            @if (is_array($log->response_data))
                                                <div class="json-cell">
                                                    <pre><code>{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                </div>
                                            @else
                                                <em>No valid data</em>
                                            @endif
                                        </td>

                                        {{-- Request Headers --}}
                                        <td class="text-start">
                                            @if(is_array($log->headers))
                                                <div class="json-cell">
                                                    <pre><code>{{ json_encode($log->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                </div>
                                            @else
                                                <em>No valid headers</em>
                                            @endif
                                        </td>

                                        {{-- Request Data --}}
                                        <td class="text-start">
                                            @if(is_array($log->request_data))
                                                <div class="json-cell">
                                                    <pre><code>{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                                </div>
                                            @else
                                                <em>No valid data</em>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- ✅ Pagination outside scroll --}}
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
    <style>
        .table-container {
            max-height: 700px;
            overflow: auto;
        }
        .log-table {
            table-layout: fixed;   /* ✅ ensures widths respected */
            min-width: 1200px;
            word-wrap: break-word;
        }
        .log-table td, .log-table th {
            white-space: normal !important;
            vertical-align: top;
        }
        /* JSON pretty box */
        .json-cell {
            max-height: 250px;       /* scroll inside cell */
            overflow-y: auto;
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            text-align: left;
        }
        .json-cell pre {
            margin: 0;
            font-size: 13px;
            line-height: 1.4;
        }
    </style>

        <style>
            .meta-info div {
                margin-bottom: 3px;
                font-size: 14px;
            }
            .meta-info strong {
                color: #0d6efd; /* Bootstrap primary color */
                font-weight: 600;
                margin-right: 4px;
            }
        </style>
@endsection

