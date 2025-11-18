@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb class="col">
        <x-slot name="title">{{ __('Monthly Token Management') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item active">{{ __('Monthly Tokens') }}</li>
        </ul>
        <x-slot name="right">
            <div class="col-auto float-end ms-auto">
                <a href="{{ route('monthly-tokens.settings') }}" class="btn add-btn">
                    <i class="fa fa-cog"></i> {{ __('Settings') }}
                </a>
            </div>
        </x-slot>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('Pending Conversion Requests') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Tokens') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conversions->where('status', 'pending') as $conversion)
                                <tr>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="#" class="avatar avatar-xs">
                                                <img src="{{ asset($conversion->user->avatar ?? 'images/user.jpg') }}" alt="{{ $conversion->user->fullname }}">
                                            </a>
                                            <a href="#">{{ $conversion->user->fullname }}</a>
                                        </h2>
                                    </td>
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
                                            <strong>₱{{ number_format($conversion->cash_amount, 2) }}</strong>
                                        @else
                                            <strong>{{ $conversion->leave_credits_added }}</strong> {{ __('credits') }}
                                        @endif
                                    </td>
                                    <td><span class="badge bg-warning">{{ __('Pending') }}</span></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal{{ $conversion->id }}">
                                                <i class="fa fa-check"></i> {{ __('Approve') }}
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $conversion->id }}">
                                                <i class="fa fa-times"></i> {{ __('Reject') }}
                                            </button>
                                        </div>

                                        <!-- Approve Modal -->
                                        <div class="modal fade" id="approveModal{{ $conversion->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Approve Conversion') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('monthly-tokens.approve', $conversion->id) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>{{ __('Approve token conversion for') }} <strong>{{ $conversion->user->fullname }}</strong>?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __('Notes (Optional)') }}</label>
                                                                <textarea name="notes" class="form-control" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="btn btn-success">{{ __('Approve') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $conversion->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">{{ __('Reject Conversion') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('monthly-tokens.reject', $conversion->id) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>{{ __('Reject token conversion for') }} <strong>{{ $conversion->user->fullname }}</strong>?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __('Reason (Optional)') }}</label>
                                                                <textarea name="notes" class="form-control" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="btn btn-danger">{{ __('Reject') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No pending requests.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('All Conversion History') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Tokens') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Approved By') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conversions as $conversion)
                                <tr>
                                    <td>{{ $conversion->user->fullname }}</td>
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
                                    <td>{{ $conversion->approver->fullname ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No conversion history.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $conversions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('Employee Monthly Token Balances') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Available Tokens') }}</th>
                                    <th>{{ __('Total Earned') }}</th>
                                    <th>{{ __('Total Converted') }}</th>
                                    <th>{{ __('Last Granted') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                <tr>
                                    <td>{{ $employee->fullname }}</td>
                                    <td><span class="badge bg-primary">{{ $employee->monthlyToken->tokens ?? 0 }}</span></td>
                                    <td>{{ $employee->monthlyToken->earned_tokens ?? 0 }}</td>
                                    <td>{{ $employee->monthlyToken->converted_tokens ?? 0 }}</td>
                                    <td>{{ $employee->monthlyToken->last_granted_month ? $employee->monthlyToken->last_granted_month->format('M Y') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
