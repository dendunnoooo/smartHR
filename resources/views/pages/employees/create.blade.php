@extends('layouts.app')

@push('page-styles')
<style>
    .form-section {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #ff902f;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .form-section-title {
        font-size: 15px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    .form-section-title i {
        margin-right: 10px;
        color: #ff902f;
        font-size: 16px;
    }
    .input-block label,
    .col-form-label {
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .required-field::after {
        content: " *";
        color: #ff902f;
        font-weight: bold;
    }
    .form-help-text {
        font-size: 11px;
        color: #8a8a8a;
        margin-top: 5px;
        display: block;
    }
    .form-control:focus,
    .select:focus {
        border-color: #ff902f;
        box-shadow: 0 0 0 0.2rem rgba(255, 144, 47, 0.25);
    }
    .btn-primary {
        background-color: #ff902f;
        border-color: #ff902f;
    }
    .btn-primary:hover {
        background-color: #e67e22;
        border-color: #e67e22;
    }
    .input-block {
        margin-bottom: 0 !important;
    }
    .status-toggle label {
        margin-bottom: 8px;
    }
    .page-wrapper {
        padding-top: 0;
    }
</style>
@endpush

@section('page-content')
<div class="content container-fluid">
    
    <!-- Page Header -->
    <x-breadcrumb>
        <x-slot name="title">{{ __('Add New Employee') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('employees.index') }}">{{ __('Employees') }}</a>
            </li>
            <li class="breadcrumb-item active">
                {{ __('Add Employee') }}
            </li>
        </ul>
    </x-breadcrumb>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('employees.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fa fa-user"></i> {{ __('Personal Information') }}
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-block mb-3">
                                        <x-form.label class="required-field">{{ __('First Name') }}</x-form.label>
                                        <x-form.input type="text" name="firstname" placeholder="Enter first name" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-block mb-3">
                                        <x-form.label>{{ __('Middle Name') }}</x-form.label>
                                        <x-form.input type="text" name="middlename" placeholder="Enter middle name (optional)" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-block mb-3">
                                        <x-form.label class="required-field">{{ __('Last Name') }}</x-form.label>
                                        <x-form.input type="text" name="lastname" placeholder="Enter last name" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fa fa-lock"></i> {{ __('Account Information') }}
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-block mb-3">
                                        <x-form.label class="required-field">{{ __('Username') }}</x-form.label>
                                        <x-form.input type="text" name="username" placeholder="Choose a unique username" required />
                                        <small class="form-help-text">Username must be unique</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-block mb-3">
                                        <x-form.label class="required-field">{{ __('Email Address') }}</x-form.label>
                                        <x-form.input type="email" name="email" placeholder="employee@smarthr.com" required />
                                        <small class="form-help-text">Employee will use this for login</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <x-form.input-block>
                                        <x-form.label class="required-field">{{ __('Password') }}</x-form.label>
                                        <x-form.input type="password" name="password" placeholder="Enter secure password" required />
                                        <small class="form-help-text">Minimum 8 characters</small>
                                    </x-form.input-block>
                                </div>
                                <div class="col-md-6">
                                    <x-form.input-block>
                                        <x-form.label class="required-field">{{ __('Confirm Password') }}</x-form.label>
                                        <x-form.input type="password" name="password_confirmation" placeholder="Re-enter password" required />
                                        <small class="form-help-text">Must match password</small>
                                    </x-form.input-block>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fa fa-phone"></i> {{ __('Contact Information') }}
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-block mb-3">
                                        <label class="required-field">{{ __('Phone Number') }}</label>
                                        <x-form.phone type="text" name="phone" placeholder="+1 234 567 8900" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-block mb-3">
                                        <x-form.label>{{ __('Address') }}</x-form.label>
                                        <x-form.input type="text" name="address" placeholder="Enter full address" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Details Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fa fa-briefcase"></i> {{ __('Employment Details') }}
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input-block>
                                        <x-form.label class="required-field">{{ __('Department') }}</x-form.label>
                                        <select name="department" id="department" class="select form-control" required>
                                            <option value="">-- {{ __('Select Department') }} --</option>
                                            @if (!empty($departments))
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <small class="form-help-text">Assign employee to a department</small>
                                    </x-form.input-block>
                                </div>
                                <div class="col-md-6">
                                    <x-form.input-block>
                                        <x-form.label class="required-field">{{ __('Designation') }}</x-form.label>
                                        <select name="designation" id="designation" class="select form-control" required>
                                            <option value="">-- {{ __('Select Designation') }} --</option>
                                            @if (!empty($designations))
                                                @foreach ($designations as $designation)
                                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <small class="form-help-text">Employee's job position</small>
                                    </x-form.input-block>
                                </div>
                                <div class="col-md-6">
                                    <x-form.input-block>
                                        <x-form.label>{{ __('Work Schedule') }}</x-form.label>
                                        <select name="schedule_id" id="schedule_id" class="select form-control">
                                            <option value="">-- {{ __('Select Schedule') }} --</option>
                                            @foreach (\App\Models\Schedule::where('is_active', true)->get() as $schedule)
                                                <option value="{{ $schedule->id }}">{{ $schedule->name }} ({{ $schedule->time_range }})</option>
                                            @endforeach
                                        </select>
                                        <small class="form-help-text">Assign work schedule</small>
                                    </x-form.input-block>
                                </div>
                            </div>
                        </div>

                        <!-- Profile & Status Section -->
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="fa fa-image"></i> {{ __('Profile & Status') }}
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="input-block mb-3">
                                        <label class="col-form-label">{{ __('Avatar') }}</label>
                                        <x-form.input type="file" name="avatar" accept="image/*" />
                                        <small class="form-help-text">Upload profile picture (JPG, PNG - Max 2MB)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="status-toggle">
                                        <x-form.label>{{ __('Active Status') }}</x-form.label>
                                        <div class="d-flex align-items-center">
                                            <x-form.input type="checkbox" id="status" class="check" name="status" checked />
                                            <label for="status" class="checktoggle ms-2">checkbox</label>
                                        </div>
                                        <small class="form-help-text">Enable employee account</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="submit-section text-end pt-3 border-top">
                            <a href="{{ route('employees.index') }}" class="btn btn-secondary me-2 px-4">
                                <i class="fa fa-times me-2"></i>{{ __('Cancel') }}
                            </a>
                            <x-form.button type="submit" class="btn btn-primary submit-btn px-4">
                                <i class="fa fa-check me-2"></i>{{ __('Create Employee') }}
                            </x-form.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('page-scripts')
@endpush
