@extends('layouts.app')


@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Payslips') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Payroll') }}
                </li>
            </ul>
            <x-slot name="right">
                <div class="col-auto float-end ms-auto">
                    @can('create-payslip')
                    <a data-url="{{ route('payslips.create') }}" href="javascript:void(0)" class="btn add-btn"
                        data-ajax-modal="true"
                        data-size="md" data-title="{{ __('Add Payslip') }}">
                        <i class="fa-solid fa-plus"></i> {{ __('Add Payslip') }}
                    </a>
                    <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
                        <i class="fa-solid fa-users"></i> {{ __('Generate for All Employees') }}
                    </button>
                    @endcan
                </div>
            </x-slot>
        </x-breadcrumb>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    {!! $dataTable->table(['class' => 'table table-striped custom-table w-100']) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Generate Modal -->
    <div class="modal fade" id="bulkGenerateModal" tabindex="-1" aria-labelledby="bulkGenerateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('payslips.bulk-generate') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkGenerateModalLabel">{{ __('Generate Payslips for All Employees') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Payslip Type') }} <span class="text-danger">*</span></label>
                            <select class="form-control" name="type" id="bulk_type" required>
                                <option value="">{{ __('Select Type') }}</option>
                                <option value="monthly">{{ __('Monthly') }}</option>
                                <option value="weekly">{{ __('Weekly') }}</option>
                                <option value="hourly">{{ __('Hourly') }}</option>
                                <option value="bi-monthly">{{ __('Bi-Monthly') }}</option>
                            </select>
                        </div>

                        <div class="alert alert-warning mb-3" id="generation_warning">
                            <small>
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                <strong>{{ __('Important:') }}</strong> {{ __('All payslips can only be generated on the 15th or last day of the month. Today is ') }}{{ now()->format('F j') }}{{ now()->day == 1 ? 'st' : (now()->day == 2 ? 'nd' : (now()->day == 3 ? 'rd' : 'th')) }}{{ __(', so generation is ') }}<strong>{{ (now()->day === 15 || now()->isLastOfMonth()) ? __('allowed') : __('not allowed') }}</strong>.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Payslip Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payslip_date" value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3" id="bulk_hourly_fields" style="display:none;">
                            <label class="form-label">{{ __('From Date') }}</label>
                            <input type="date" class="form-control" name="from_date">
                            <label class="form-label mt-2">{{ __('To Date') }}</label>
                            <input type="date" class="form-control" name="to_date">
                        </div>

                        <div class="mb-3" id="bulk_weekly_fields" style="display:none;">
                            <label class="form-label">{{ __('Number of Weeks') }}</label>
                            <input type="number" class="form-control" name="weeks" min="1" placeholder="4">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Title (optional)') }}</label>
                            <input type="text" class="form-control" name="title" placeholder="{{ __('e.g., November 2025 Salary') }}">
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_email" value="1" id="send_email_check" checked>
                            <label class="form-check-label" for="send_email_check">
                                {{ __('Send email notification to employees') }}
                            </label>
                        </div>

                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fa-solid fa-info-circle"></i>
                                {{ __('This will create payslips for all active employees who have salary details configured. Allowances and deductions from salary settings will be applied automatically.') }}
                            </small>
                        </div>

                        <div class="alert alert-warning mt-2">
                            <small>
                                <i class="fa-solid fa-exclamation-triangle"></i>
                                <strong>{{ __('Note:') }}</strong> {{ __('Newly employed employees (7 days or less) will be skipped and included in the next payslip generation cycle.') }}
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Generate Payslips') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('page-scripts')
@vite([
    "resources/js/datatables.js"
])
{!! $dataTable->scripts(attributes: ['type' => 'module']) !!}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('bulk_type');
        const hourlyFields = document.getElementById('bulk_hourly_fields');
        const weeklyFields = document.getElementById('bulk_weekly_fields');

        if(typeSelect) {
            typeSelect.addEventListener('change', function() {
                hourlyFields.style.display = this.value === 'hourly' ? 'block' : 'none';
                weeklyFields.style.display = this.value === 'weekly' ? 'block' : 'none';

                // Set required attributes
                const fromDate = document.querySelector('[name="from_date"]');
                const toDate = document.querySelector('[name="to_date"]');
                const weeks = document.querySelector('[name="weeks"]');

                if(this.value === 'hourly') {
                    fromDate.required = true;
                    toDate.required = true;
                    weeks.required = false;
                } else if(this.value === 'weekly') {
                    weeks.required = true;
                    fromDate.required = false;
                    toDate.required = false;
                } else {
                    fromDate.required = false;
                    toDate.required = false;
                    weeks.required = false;
                }
            });
        }
    });
</script>
@endpush

