@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ __('My Attendance') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Attendance') }}
                </li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <!-- Attendance Timesheet -->
        <livewire:employee-attendance />

    </div>
@endsection

@push('page-scripts')
@endpush
