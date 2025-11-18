@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb>
        <x-slot name="title">{{ __('Monthly Token Settings') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('monthly-tokens.index') }}">{{ __('Monthly Tokens') }}</a></li>
            <li class="breadcrumb-item active">{{ __('Settings') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('Token Conversion Rates') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('monthly-tokens.settings.update') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label">{{ __('Token to Cash Value (PHP)') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="token_to_cash" class="form-control" 
                                    value="{{ \App\Models\TokenSetting::get('token_to_cash', 500) }}" 
                                    step="0.01" min="0" required>
                            </div>
                            <small class="text-muted">{{ __('Amount in PHP that employees receive per token when converting to cash') }}</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">{{ __('Token to Leave Credits') }}</label>
                            <input type="number" name="token_to_leave_credits" class="form-control" 
                                value="{{ \App\Models\TokenSetting::get('token_to_leave_credits', 2) }}" 
                                min="1" required>
                            <small class="text-muted">{{ __('Number of leave credits employees receive per token') }}</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>{{ __('Note:') }}</strong> {{ __('These rates will apply to all future token conversions.') }}
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> {{ __('Save Settings') }}
                        </button>
                        <a href="{{ route('monthly-tokens.index') }}" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </a>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('About Monthly Tokens') }}</h4>
                </div>
                <div class="card-body">
                    <h5>{{ __('How It Works:') }}</h5>
                    <ul>
                        <li>{{ __('Employees earn 1 monthly token for perfect attendance throughout a month') }}</li>
                        <li>{{ __('Perfect attendance = Present on all working days (Monday-Friday)') }}</li>
                        <li>{{ __('Tokens are granted automatically on the 1st of each month') }}</li>
                        <li>{{ __('Employees can convert tokens to cash or leave credits') }}</li>
                        <li>{{ __('All conversion requests require HR approval') }}</li>
                    </ul>

                    <h5 class="mt-4">{{ __('Granting Tokens Manually:') }}</h5>
                    <p>{{ __('Run the following command to check and grant tokens:') }}</p>
                    <code>php artisan monthly-tokens:grant</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
