@if(request()->ajax())
    @include('leaves._form')
@else
    @extends('layouts.app')

    @section('page-content')
        <div class="content container-fluid">
            <!-- Page Header -->
            <x-breadcrumb class="col">
                <x-slot name="title">{{ __('Apply for Leave') }}</x-slot>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ __('Leaves') }}
                    </li>
                </ul>
            </x-breadcrumb>
            <!-- /Page Header -->

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            @include('leaves._form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@endif
