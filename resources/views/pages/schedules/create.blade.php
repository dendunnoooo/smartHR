@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ __('Create Schedule') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('schedules.index') }}">{{ __('Schedules') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Create') }}</li>
            </ul>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">{{ __('Schedule Information') }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('schedules.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('Schedule Name') }}</x-form.label>
                                        <x-form.input type="text" name="name" value="{{ old('name') }}" placeholder="e.g., Day Shift, Night Shift" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-4">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('Start Time') }}</x-form.label>
                                        <x-form.input type="time" name="start_time" value="{{ old('start_time') }}" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-4">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('End Time') }}</x-form.label>
                                        <x-form.input type="time" name="end_time" value="{{ old('end_time') }}" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-4">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('Work Hours') }}</x-form.label>
                                        <x-form.input type="number" name="work_hours" value="{{ old('work_hours', 8) }}" min="1" max="24" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-12">
                                    <x-form.input-block>
                                        <x-form.label>{{ __('Working Days') }}</x-form.label>
                                        <div class="row">
                                            @foreach($days as $day)
                                                <div class="col-md-3 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" id="day_{{ $day }}" {{ in_array($day, old('days', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="day_{{ $day }}">
                                                            {{ __($day) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-12">
                                    <x-form.input-block>
                                        <x-form.label>{{ __('Description') }}</x-form.label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="{{ __('Enter schedule description') }}">{{ old('description') }}</textarea>
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-12">
                                    <x-form.input-block>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ __('Active Schedule') }}
                                            </label>
                                        </div>
                                    </x-form.input-block>
                                </div>
                            </div>

                            <div class="submit-section">
                                <x-form.button class="btn btn-primary submit-btn">
                                    <i class="fa fa-save"></i> {{ __('Create Schedule') }}
                                </x-form.button>
                                <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
