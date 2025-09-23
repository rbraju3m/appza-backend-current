@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" style="margin-bottom: 50px !important;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6>{{ __('messages.Message') }}</h6>
                        <a href="{{ route('license_message_add') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-plus-circle"></i> {{ __('messages.createNew') }}
                        </a>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <!-- Search Form -->
                        <form method="GET" action="{{ route('license_message_list') }}" id="search-form" class="mb-3">
                            <input type="hidden" name="tab" value="{{ $activeTab }}"> {{-- preserve active tab --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ $search }}">
                                    <input type="hidden" name="tab" class="form-control tab_search_field" placeholder="Search..." value="{{ $activeTab }}">
                                </div>
                                <div class="col-md-4">
                                    <select name="license_type" class="form-control">
                                        <option value="">Choose license type</option>
                                        <option value="free_trial" {{ $licenseType=='free_trial'?'selected':'' }}>Free Trial</option>
                                        <option value="premium" {{ $licenseType=='premium'?'selected':'' }}>Premium</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="{{ route('license_message_list', ['tab' => $activeTab]) }}" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>


                        <!-- Product Tabs -->
                        <ul class="nav nav-tabs" id="productTabs" role="tablist">
                            @foreach($products as $product)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link tabChange {{ ($activeTab == $product->product_slug) ? 'active' : '' }}"
                                            id="tab-{{ $product->product_slug }}-tab"
                                            data-bs-toggle="tab"
                                            tab-name="{{ $product->product_slug }}"
                                            data-bs-target="#tab-{{ $product->product_slug }}"
                                            type="button" role="tab"
                                            aria-controls="tab-{{ $product->product_slug }}"
                                            aria-selected="{{ ($activeTab == $product->product_slug) ? 'true' : 'false' }}">
                                        {{ $product->product_name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <!-- Tab Panes -->
                        <div class="tab-content mt-3">
                            @foreach($products as $product)
                                <div class="tab-pane fade {{ ($activeTab == $product->product_slug) ? 'show active' : '' }}"
                                     id="tab-{{ $product->product_slug }}" role="tabpanel"
                                     aria-labelledby="tab-{{ $product->product_slug }}-tab">

                                    <table class="table table-bordered table-striped text-center">
                                        <thead class="thead-dark">
                                        <tr>
                                            <th>{{ __('messages.SL') }}</th>
                                            <th>{{ __('messages.ProductName') }}</th>
                                            <th>{{ __('messages.LicenseType') }}</th>
                                            <th>{{ __('messages.Matrix') }}</th>
                                            <th>{{ __('messages.Message') }}</th>
                                            <th><i class="fas fa-cog"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $currentPage = $licenseMessages[$product->product_slug]->currentPage();
                                            $perPage = $licenseMessages[$product->product_slug]->perPage();
                                            $serial = ($currentPage - 1) * $perPage + 1;
                                        @endphp
                                        @foreach($licenseMessages[$product->product_slug] as $message)
                                            <tr>
                                                <td>{{ $serial++ }}</td>
                                                <td>{{ $message->product->product_name }}</td>
                                                <td>{{ $message->license_type=='free_trial'?'Free Trial':'Premium' }}</td>
                                                <td>{{ $message->logic->name ?? '-' }}</td>
                                                <td style="text-align:left">
                                                    @foreach($message->message_details as $detail)
                                                        <div class="mb-1">
                                                            <span class="badge bg-warning">
                                                                @if($detail->type == 'user')
                                                                    User
                                                                @elseif($detail->type == 'admin')
                                                                    Admin
                                                                @elseif($detail->type == 'special')
                                                                    Special
                                                                @endif
                                                            </span>
                                                            {{ $detail->message }}
                                                        </div>
                                                    @endforeach
                                                </td>

                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('license_message_edit', $message->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <div class="d-flex justify-content-end">
                                        {{ $licenseMessages[$product->product_slug]->appends(['tab' => $product->product_slug])->links('layouts.pagination') }}
{{--                                        {{ $licenseMessages[$product->product_slug]->appends(request()->query())->links('layouts.pagination') }}--}}

                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('CustomStyle')
    <style>
        .mg-top { margin-top: 10px; }
    </style>
@endpush

@section('footer.scripts')
    <script>
        // Keep active tab on reload
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = '{{ $activeTab }}';
            console.log(activeTab)
            if(activeTab) {
                const tabEl = document.getElementById(`tab-${activeTab}-tab`);
                if(tabEl) new bootstrap.Tab(tabEl).show();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Select all tab buttons with class 'tabChange'
            const tabs = document.querySelectorAll('.tabChange');

            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    const tabName = this.getAttribute('tab-name'); // get tab identifier
                    $('.tab_search_field').val(tabName)
                });
            });
        });

    </script>
@endsection
