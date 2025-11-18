@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb>
            <x-slot name="title">{{ __('Edit Schedule') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('schedules.index') }}">{{ __('Schedules') }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __('Edit') }}</li>
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
                        <form action="{{ route('schedules.update', $schedule) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('Schedule Name') }}</x-form.label>
                                        <x-form.input type="text" name="name" value="{{ old('name', $schedule->name) }}" placeholder="e.g., Day Shift, Night Shift" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-4">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('Start Time') }}</x-form.label>
                                        <x-form.input type="time" name="start_time" value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-4">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('End Time') }}</x-form.label>
                                        <x-form.input type="time" name="end_time" value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-4">
                                    <x-form.input-block>
                                        <x-form.label required>{{ __('Work Hours') }}</x-form.label>
                                        <x-form.input type="number" name="work_hours" value="{{ old('work_hours', $schedule->work_hours) }}" min="1" max="24" required />
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-12">
                                    <x-form.input-block>
                                        <x-form.label>{{ __('Working Days') }}</x-form.label>
                                        <div class="row">
                                            @php
                                                $selectedDays = old('days', is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true) ?? []);
                                            @endphp
                                            @foreach($days as $day)
                                                <div class="col-md-3 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="days[]" value="{{ $day }}" id="day_{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
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
                                        <textarea name="description" class="form-control" rows="3" placeholder="{{ __('Enter schedule description') }}">{{ old('description', $schedule->description) }}</textarea>
                                    </x-form.input-block>
                                </div>

                                <div class="col-md-12">
                                    <x-form.input-block>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $schedule->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                {{ __('Active Schedule') }}
                                            </label>
                                        </div>
                                    </x-form.input-block>
                                </div>
                            </div>

                            <!-- Schedule Rotation Settings -->
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fa fa-sync-alt"></i> {{ __('Automatic Schedule Rotation') }}
                                    </h5>
                                    <small class="text-muted">{{ __('Configure automatic schedule rotation for employees assigned to this schedule') }}</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-form.input-block>
                                                <x-form.label>{{ __('Rotation Day (1-31)') }}</x-form.label>
                                                <x-form.input type="number" name="rotation_day" value="{{ old('rotation_day', $schedule->rotation_day) }}" min="1" max="31" placeholder="{{ __('e.g., 1 for first day of month') }}" />
                                                <small class="text-muted">{{ __('Day of the month to rotate schedules. Leave empty to disable rotation.') }}</small>
                                            </x-form.input-block>
                                        </div>

                                        <div class="col-md-6">
                                            <x-form.input-block>
                                                <x-form.label>{{ __('Next Schedule') }}</x-form.label>
                                                <select name="next_schedule_id" class="form-control">
                                                    <option value="">{{ __('-- No Rotation --') }}</option>
                                                    @foreach(\App\Models\Schedule::where('id', '!=', $schedule->id)->where('is_active', true)->get() as $nextSchedule)
                                                        <option value="{{ $nextSchedule->id }}" {{ old('next_schedule_id', $schedule->next_schedule_id) == $nextSchedule->id ? 'selected' : '' }}>
                                                            {{ $nextSchedule->name }} ({{ $nextSchedule->time_range }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">{{ __('Schedule to rotate employees to on the rotation day') }}</small>
                                            </x-form.input-block>
                                        </div>
                                    </div>

                                    @if($schedule->rotation_day && $schedule->nextSchedule)
                                        <div class="alert alert-info mt-3">
                                            <i class="fa fa-info-circle"></i> 
                                            {{ __('Employees with this schedule will automatically switch to') }} <strong>{{ $schedule->nextSchedule->name }}</strong> {{ __('on day') }} <strong>{{ $schedule->rotation_day }}</strong> {{ __('of each month.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="submit-section mt-4">
                                <x-form.button class="btn btn-primary submit-btn">
                                    <i class="fa fa-save"></i> {{ __('Update Schedule') }}
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
