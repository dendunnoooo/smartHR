@extends('layouts.app')

@section('page-content')
<div class="content container-fluid">
    <x-breadcrumb>
        <x-slot name="title">{{ __('Leave Credits') }}</x-slot>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item active">{{ __('Settings') }}</li>
        </ul>
    </x-breadcrumb>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <form method="POST" action="{{ route('settings.leave_tokens.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('Employee') }}</label>
                            <select name="user_id" class="form-control">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->email }}) - Credits: {{ $emp->leave_tokens ?? 0 }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Leave Credits') }}</label>
                            <input type="number" name="leave_tokens" min="0" max="1000" value="0" class="form-control" />
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">{{ __('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
