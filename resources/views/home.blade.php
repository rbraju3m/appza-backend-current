@extends('layouts.app')

@section('body')
    <style>
        /* CSS to make the card height 100% */
        .full-height-card {
            min-height: 80vh;
            display: flex;
            flex-direction: column;
        }
        .full-height-card .card-body {
            flex-grow: 1;
        }
    </style>

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                @include('layouts.message')

                <div class="card full-height-card">
                    <div class="card-header">{{ __('messages.dashboard') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{ __('messages.youAreLogin') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
