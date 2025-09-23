@extends('layouts.app')

@section('body')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                            <h6>{{__('messages.createNewMatrix')}}</h6>
                            <div class="btn-toolbar mb-2 mb-md-0">
                                <div class="btn-group me-2">
                                    <a href="{{route('license_logic_list')}}" class="module_button_header">
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
                            <div class="col-md-12">
                                {{ html()
                                    ->form('POST', route('license_logic_store'))
                                    ->attribute('enctype', 'multipart/form-data')
                                    ->attribute('files', true)
                                    ->attribute('autocomplete', 'off')
                                    ->open()
                                }}
                                <div class="row">

                                    <!-- Name & Identifier -->
                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="name" class="form-label">{{__('messages.name')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->text('name')
                                                ->class('form-control')
                                                ->placeholder(__('messages.name'))
                                                ->value(old('name'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('name') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="slug" class="form-label">{{__('messages.Identifier')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->text('slug')
                                                ->class('form-control')
                                                ->placeholder(__('messages.Identifier'))
                                                ->value(old('slug'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('slug') !!}</span>
                                        </div>
                                    </div>

                                    <!-- Event -->
                                    <div class="form-group row mg-top">
                                        <div class="col-sm-2">
                                            <label for="event" class="form-label">{{__('messages.event')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->select('event', $eventDropdown, old('event'))
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                                ->placeholder(__('messages.ChooseEvent'))
                                                ->required()
                                            }}
                                            <span class="textRed">{!! $errors->first('event') !!}</span>
                                            {{-- Show composite uniqueness error --}}
                                            @if ($errors->has('event_combination'))
                                                <span class="textRed">{{ $errors->first('event_combination') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <hr style="margin-top: 20px">

                                    <!-- Direction (conditionally shown) -->
                                    <div class="form-group row mg-top" id="directionRow">
                                        <div class="col-sm-2">
                                            <label for="direction" class="form-label">{{__('messages.Direction')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->select('direction', $directionDropdown)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                            }}
                                            <span class="textRed">{!! $errors->first('direction') !!}</span>
                                        </div>
                                    </div>

                                    <!-- From & To Days (conditionally shown) -->
                                    <div class="form-group row mg-top" id="daysRow">
                                        <div class="col-sm-2">
                                            <label for="from_days" class="form-label">{{__('messages.FromDays')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->select('from_days', $toDaysDropdown)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                            }}
                                            <span class="textRed">{!! $errors->first('from_days') !!}</span>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="to_days" class="form-label">{{__('messages.ToDays')}}</label>
                                            <span class="textRed">*</span>
                                        </div>
                                        <div class="col-sm-4">
                                            {{ html()->select('to_days', $toDaysDropdown)
                                                ->class('form-control form-select js-example-basic-single')
                                                ->attribute('aria-describedby', 'basic-addon2')
                                            }}
                                            <span class="textRed">{!! $errors->first('to_days') !!}</span>
                                        </div>
                                    </div>

                                    <!-- Submit / Reset -->
                                    <div class="row mg-top">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-10">
                                            <div class="from-group">
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                                <button type="reset" class="btn submit-button">Reset</button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                {{ html()->form()->close() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('CustomStyle')
    <style>
        .textRed { color: #ff0000; }
        .mg-top { margin-top: 10px; }
    </style>
@endpush

@section('footer.scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            function toggleConditionalFields() {
                const eventVal = $('select[name="event"]').val();
                const show = (eventVal === 'expiration' || eventVal === 'grace');

                $('#directionRow').toggle(show);
                $('#daysRow').toggle(show);

                $('select[name="direction"]').attr('required', show);
                $('select[name="from_days"]').attr('required', show);
                $('select[name="to_days"]').attr('required', show);
            }

            // Initial check (for old values)
            toggleConditionalFields();

            // On event change
            $('select[name="event"]').change(function() {
                toggleConditionalFields();
            });
        });
    </script>
@endsection
