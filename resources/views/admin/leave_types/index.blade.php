@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">
        <x-breadcrumb>
            <x-slot name="title">{{ __('Leave Types') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Leave Types') }}</li>
            </ul>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <a href="{{ route('leave-types.create') }}" class="btn btn-primary mb-3">{{ __('Create Leave Type') }}</a>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Max Days') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $t)
                                    <tr>
                                        <td>{{ $t->name }}</td>
                                        <td>{{ $t->code }}</td>
                                        <td>{{ $t->max_days ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('leave-types.edit', $t) }}" class="btn btn-sm btn-secondary">{{ __('Edit') }}</a>
                                            <form action="{{ route('leave-types.destroy', $t) }}" method="POST" style="display:inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Delete?') }}')">{{ __('Delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
