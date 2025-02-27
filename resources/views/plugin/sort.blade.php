@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.themeSort')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">

                                    <a href="{{route('plugin_list')}}" title="" class="module_button_header">
                                        <button type="button" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i> {{__('messages.list')}}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @include('layouts.message')

                        <div class="row">

                            <div class="portlet light bordered">
                                <div class="portlet-body form">
                                    <div class="form-body">
                                        <h3>Drag and Drop to Sort the Events</h3>
                                        <div id="plugin_sort_data_div">

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('CustomStyle')

    <!-- CSS -->

    <style>

        .portlet.light.bordered {
            border: 1px solid #ddd;
            border-top: 3px solid #4b77be;
            border-radius: 4px;
            background-color: #fff;
            margin-bottom: 25px;
            padding: 20px;
        }

        .portlet-body.form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-body {
            width: 100%;
        }

        .form-body h3 {
            text-align: center;
            color: #333;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        #plugin_sort_data_div {
            width: 100%;
            max-width: 600px;
            margin: auto;
        }

        #sortable {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        #sortable .ui-sortable-handle {
            border: 1px solid #ddd;
            padding: 10px 15px;
            margin-bottom: 10px;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Start list item content on the left */
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        #sortable .ui-sortable-handle i.fa.fa-sort {
            margin-right: 10px;
            color: #4b77be;
        }

    </style>
@endpush

@section('footer.scripts')

    <script>
        $(document).ready(function () {
            refresh_club_sort_data();
        });
        function refresh_club_sort_data() {
            $.ajax({
                type: "GET",
                url: "{{ route('plugin_sort_data') }}",

                success: function (responseData) {
                    $("#plugin_sort_data_div").html('');
                    $("#plugin_sort_data_div").html(responseData);
                    /**************************/
                    $('#sortable').sortable({
                        update: function (event, ui) {
                            var pluginOrder = $(this).sortable('toArray').toString();
                            $.post("{{ route('plugin_sort_update') }}", {pluginOrder: pluginOrder, _method: 'PUT', _token: '{{ csrf_token() }}'})
                        }
                    });
                    $("#sortable").disableSelection();
                }
            });
        }
    </script>

@endsection
