@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb>
        <x-slot name="title">{{ __('Monthly Attendance Tokens') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item active">{{ __('Monthly Tokens') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fa fa-trophy fa-4x text-warning"></i>
                    </div>
                    <h2 class="text-primary">{{ $monthlyToken->tokens }}</h2>
                    <p class="text-muted">{{ __('Available Tokens') }}</p>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <h5>{{ $monthlyToken->earned_tokens }}</h5>
                            <small class="text-muted">{{ __('Total Earned') }}</small>
                        </div>
                        <div class="col-6">
                            <h5>{{ $monthlyToken->converted_tokens }}</h5>
                            <small class="text-muted">{{ __('Converted') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('Convert Tokens') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('monthly-tokens.convert') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Number of Tokens') }}</label>
                                <input type="number" name="tokens" class="form-control" min="1" max="{{ $monthlyToken->tokens }}" value="1" required>
                                <small class="text-muted">{{ __('Available:') }} {{ $monthlyToken->tokens }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Convert To') }}</label>
                                <select name="conversion_type" class="form-control" id="conversionType" required>
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="leave_credits">{{ __('Leave Credits') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <strong>{{ __('Conversion Rates:') }}</strong>
                            <ul class="mb-0 mt-2">
                                <li>1 Token = ₱{{ number_format($tokenToCash, 2) }}</li>
                                <li>1 Token = {{ $tokenToCredits }} Leave Credits</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i>
                            {{ __('Your request will be sent to HR for approval.') }}
                        </div>

                        <button type="submit" class="btn btn-primary" {{ $monthlyToken->tokens == 0 ? 'disabled' : '' }}>
                            <i class="fa fa-exchange-alt"></i> {{ __('Submit Conversion Request') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('Conversion History') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Tokens') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conversions as $conversion)
                                <tr>
                                    <td>{{ $conversion->converted_at->format('M d, Y H:i') }}</td>
                                    <td><span class="badge bg-warning">{{ $conversion->tokens_converted }}</span></td>
                                    <td>
                                        @if($conversion->conversion_type === 'cash')
                                            <i class="fa fa-money-bill text-success"></i> {{ __('Cash') }}
                                        @else
                                            <i class="fa fa-calendar text-info"></i> {{ __('Leave Credits') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($conversion->conversion_type === 'cash')
                                            ₱{{ number_format($conversion->cash_amount, 2) }}
                                        @else
                                            {{ $conversion->leave_credits_added }} {{ __('credits') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($conversion->status === 'pending')
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @elseif($conversion->status === 'approved')
                                            <span class="badge bg-success">{{ __('Approved') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $conversion->notes }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No conversion history yet.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
