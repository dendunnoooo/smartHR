@extends('layouts.app')

@push('page-styles')
<style>
    table.dataTable td:nth-child(4) {
        max-width: 400px;
        white-space: normal;
        word-wrap: break-word;
    }
    table.dataTable th:nth-child(4) {
        width: 40%;
    }
</style>
@endpush

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Departments') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Department') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    <a data-url="{{ route('departments.create') }}" href="javascript:void(0)" class="btn add-btn"
                        data-ajax-modal="true"
                        data-size="md" data-title="Add Department">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Department') }}
                    </a>
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Search Filter -->
        <!-- /Search Filter -->

        <div class="row">
            <div class="col-md-12">
                {!! $dataTable->table(['class' => 'table table-striped custom-table w-100']) !!}
            </div>
        </div>
    </div>
@endsection


@push('page-scripts')
@vite([
    "resources/js/datatables.js"
])
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
@endpush

