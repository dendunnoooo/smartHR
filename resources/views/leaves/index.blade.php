@extends('layouts.app')

@section('page-content')
    <div class="content container-fluid">

        <!-- Page Header -->
        @php
            $authUser = auth()->user();
            $isAdminView = false;
            if ($authUser) {
                if (method_exists($authUser, 'hasRole')) {
                    $adminNames = ['admin', 'administrator', 'super admin', 'super-admin', 'superadmin'];
                    foreach ($adminNames as $r) {
                        if ($authUser->hasRole($r)) { $isAdminView = true; break; }
                    }
                }
                if (!$isAdminView && method_exists($authUser, 'getRoleNames')) {
                    foreach ($authUser->getRoleNames() as $rn) {
                        if (stripos($rn, 'admin') !== false) { $isAdminView = true; break; }
                    }
                }
            }
        @endphp
        <x-breadcrumb class="col">
            <x-slot name="title">{{ __('Leaves') }}</x-slot>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                </li>
                <li class="breadcrumb-item active">
                    {{ __('Leaves') }}
                </li>
            </ul>
            <x-slot name="right">
                        <div class="col-auto float-end ms-auto">
                    @php
                        $showAdd = false;
                        if(auth()->check()){
                            $u = auth()->user();
                            if(isset($u->type) && $u->type === \App\Enums\UserType::EMPLOYEE) $showAdd = true;
                            if(method_exists($u, 'hasRole') && ($u->hasRole('Admin') || $u->hasRole('Super Admin'))) $showAdd = true;
                        }
                    @endphp
                    @if($showAdd)
                        {{-- Allow employees and admins to open the create page --}}
                        <a href="{{ route('leaves.create') }}" class="btn add-btn">
                            <i class="fa-solid fa-plus"></i> {{ __('Add Leave') }}
                        </a>
                    @endif
                </div>
            </x-slot>
        </x-breadcrumb>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="leaves-table" class="table table-striped custom-table w-100">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Start') }}</th>
                                <th>{{ __('End') }}</th>
                                <th>{{ __('Days') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Rows are rendered by controller/view data --}}
                            @foreach($leaves ?? [] as $leave)
                                <tr>
                                    <td>{{ $leave->user->name ?? $leave->employee_name ?? '-' }}</td>
                                    <td>{{ $leave->leaveType->name ?? ($leave->type ?? '-') }}</td>
                                    <td>{{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') : '-' }}</td>
                                    <td>{{ $leave->days ?? ($leave->total_days ?? '-') }}</td>
                                    <td>{{ isset($leave->status) ? ucfirst(strtolower($leave->status)) : __('Pending') }}</td>
                                    <td class="text-end">
                                        {{-- Actions dropdown: vertical three-dot menu --}}
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle no-caret" type="button" id="leaveActionsDropdown{{ $leave->id }}" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="leaveActionsDropdown{{ $leave->id }}">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('leaves.show', $leave->id) }}"><i class="fa-solid fa-eye me-2"></i>{{ __('View') }}</a>
                                                </li>
                                                @if($isAdminView)
                                                    <li>
                                                        <button type="button" class="dropdown-item approve-leave-btn" data-action="{{ route('leaves.approve', $leave->id) }}"><i class="fa-solid fa-check text-success me-2"></i>{{ __('Approve') }}</button>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item reject-leave-btn" data-action="{{ route('leaves.reject', $leave->id) }}" data-leave-id="{{ $leave->id }}"><i class="fa-solid fa-xmark text-warning me-2"></i>{{ __('Reject') }}</button>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item open-delete-modal" data-action="{{ route('leaves.destroy', $leave->id) }}" data-title="{{ $leave->user->name ?? '' }}" data-start="{{ $leave->start_date }}" data-end="{{ $leave->end_date }}"><i class="fa-solid fa-trash text-danger me-2"></i>{{ __('Delete') }}</button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Delete confirmation modal --}}
        <div class="modal fade custom-confirm-modal" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body text-center pt-4 pb-3">
                            <h5 class="modal-title mb-2">{{ __('Delete Leave') }}</h5>
                            <p class="modal_message text-muted small mb-4">{{ __('Are you sure you want to delete?') }}</p>

                            <div class="d-flex justify-content-center gap-3">
                                <button type="button" class="btn btn-outline-accent btn-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="button" id="confirmDeleteBtn" class="btn btn-accent btn-pill">{{ __('Delete') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject modal --}}
        <div class="modal fade custom-confirm-modal" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="">
                        @csrf
                        <div class="modal-body text-center pt-4 pb-3">
                            <h5 class="modal-title mb-2">{{ __('Reject leave') }}</h5>
                            <p class="text-muted small mb-3">{{ __('Provide an optional comment and confirm rejection.') }}</p>
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" rows="3" placeholder="{{ __('Comment (optional)') }}"></textarea>
                            </div>
                            <div class="d-flex justify-content-center gap-3">
                                <button type="button" class="btn btn-outline-accent btn-pill" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="button" id="rejectSubmitBtn" class="btn btn-accent btn-pill">{{ __('Reject') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('page-scripts')
@vite([
    "resources/js/datatables.js",
])
<script>
    (function(){
        document.addEventListener('DOMContentLoaded', function(){
            // Initialize DataTable if available
            if(window.jQuery && typeof jQuery.fn.DataTable !== 'undefined'){
                const table = jQuery('#leaves-table').DataTable({
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [],
                    pageLength: 10,
                    lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
                    columnDefs: [ { orderable: false, targets: -1 } ]
                });
                window.leavesTable = table;
            }

            // Helper to safely update status cell. Status is the 6th column (1-based), index 5 for DataTables API.
            function updateRowStatus(rowNode, text){
                try{
                    if(window.leavesTable && typeof window.leavesTable.cell === 'function'){
                        window.leavesTable.cell(rowNode, 5).data(text);
                        window.leavesTable.draw(false);
                    } else if(rowNode){
                        const statusCell = rowNode.querySelector('td:nth-child(6)');
                        if(statusCell) statusCell.textContent = text;
                    }
                }catch(e){
                    try{ const statusCell = rowNode.querySelector('td:nth-child(6)'); if(statusCell) statusCell.textContent = text; }catch(_){ }
                }
            }
            
            // Helpers to show/hide modal when Bootstrap JS isn't available
            function manualShowModal(modalEl){
                if(!modalEl) return;
                try{ console.debug('[leaves] manualShowModal'); }catch(e){}
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                modalEl.setAttribute('aria-modal','true');
                modalEl.removeAttribute('aria-hidden');
                if(!document.querySelector('.modal-backdrop')){
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
            }
            function manualHideModal(modalEl){
                if(!modalEl) return;
                try{ console.debug('[leaves] manualHideModal'); }catch(e){}
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.setAttribute('aria-hidden','true');
                modalEl.removeAttribute('aria-modal');
                const backdrop = document.querySelector('.modal-backdrop');
                if(backdrop) backdrop.parentNode.removeChild(backdrop);
            }

            // Event delegation for actions (works across DataTable redraws)
            document.addEventListener('click', function(e){
                // Approve
                const approveBtn = e.target.closest('.approve-leave-btn');
                if(approveBtn){
                    e.preventDefault();
                    const action = approveBtn.getAttribute('data-action');
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(function(res){ if(res.ok) return res.json().catch(()=>({ success: true })); return res.text().then(function(txt){ throw new Error(txt || 'Network response was not ok.'); }); })
                    .then(function(json){ const row = approveBtn.closest('tr'); updateRowStatus(row, 'Approved'); const alertBox = document.createElement('div'); alertBox.className = 'alert alert-success'; alertBox.textContent = (json && json.message) ? json.message : '{{ __('Leave approved') }}'; document.querySelector('.content.container-fluid').prepend(alertBox); setTimeout(()=> alertBox.remove(), 3000); })
                    .catch(function(err){ console.error(err); const alertBox = document.createElement('div'); alertBox.className = 'alert alert-danger'; alertBox.textContent = err && err.message ? err.message : '{{ __('An error occurred while approving the leave') }}'; document.querySelector('.content.container-fluid').prepend(alertBox); setTimeout(()=> alertBox.remove(), 5000); });
                    return;
                }

                // Open Reject modal
                const rejectBtn = e.target.closest('.reject-leave-btn');
                if(rejectBtn){
                    console.debug('[leaves] clicked reject button', rejectBtn);
                    e.preventDefault();
                    const action = rejectBtn.getAttribute('data-action');
                    const rejectModalEl = document.getElementById('rejectModal');
                    if(rejectModalEl){
                        const form = rejectModalEl.querySelector('form');
                            if(form) {
                                form.setAttribute('action', action); 
                                console.debug('[leaves] set reject form action to', action); 

                                // Attach direct handler to the reject submit button to ensure it fires
                                const rejectBtnDirect = document.getElementById('rejectSubmitBtn');
                                if(rejectBtnDirect) {
                                    rejectBtnDirect.onclick = function(ev) {
                                        ev.preventDefault();
                                        try { console.debug('[leaves] direct reject submit clicked'); } catch(e) {}
                                        const formEl = rejectModalEl.querySelector('form'); 
                                        if(!formEl) return; 
                                        const actionUrl = formEl.getAttribute('action'); 
                                        if(!actionUrl) return; 
                                        const commentEl = formEl.querySelector('textarea[name="comment"]'); 
                                        const comment = commentEl ? commentEl.value : ''; 
                                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''; 
                                        const body = new URLSearchParams(); 
                                        if(comment) body.append('comment', comment);
                                        rejectBtnDirect.disabled = true;
                                        fetch(actionUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
                                        .then(function(res) { 
                                            if(res.ok) return res.json().catch(()=>({ success: true })); 
                                            return res.text().then(function(txt) { throw new Error(txt || 'Network response was not ok.'); }); 
                                        })
                                            .then(function(json) { 
                                            try { 
                                                const leaveId = formEl.querySelector('[name="leave_id"]') ? formEl.querySelector('[name="leave_id"]').value : null; 
                                                const btn = document.querySelector('[data-leave-id="' + (leaveId || '') + '"]'); 
                                                const row = btn ? btn.closest('tr') : null; 
                                                if(row) { updateRowStatus(row, 'Rejected'); } 
                                            } catch(e) {} 
                                            try { 
                                                if(typeof bootstrap !== 'undefined') { 
                                                    const m = bootstrap.Modal.getInstance(rejectModalEl); 
                                                    if(m) m.hide(); 
                                                } else if (window.jQuery && typeof jQuery.fn.modal === 'function') { 
                                                    jQuery(rejectModalEl).modal('hide'); 
                                                } else { 
                                                    manualHideModal(rejectModalEl); 
                                                }
                                            } catch(e) {} 
                                            const alertBox = document.createElement('div'); 
                                            alertBox.className = 'alert alert-warning'; 
                                            alertBox.textContent = (json && json.message) ? json.message : '{{ __('Leave rejected') }}'; 
                                            document.querySelector('.content.container-fluid').prepend(alertBox); 
                                            setTimeout(()=> alertBox.remove(), 3000); 
                                        })
                                        .catch(function(err) { 
                                            console.error(err); 
                                            const alertBox = document.createElement('div'); 
                                            alertBox.className = 'alert alert-danger'; 
                                            alertBox.textContent = err && err.message ? err.message : '{{ __('An error occurred while rejecting the leave') }}'; 
                                            document.querySelector('.content.container-fluid').prepend(alertBox); 
                                            setTimeout(()=> alertBox.remove(), 5000); 
                                        })
                                        .finally(function() { rejectBtnDirect.disabled = false; });
                                    };
                                }
                        }
                        if(typeof bootstrap !== 'undefined'){ const modal = new bootstrap.Modal(rejectModalEl); modal.show(); } else if (window.jQuery && typeof jQuery.fn.modal === 'function') { jQuery(rejectModalEl).modal('show'); } else { try{ console.warn('[leaves] bootstrap not present, using manualShowModal fallback'); }catch(e){} manualShowModal(rejectModalEl); }
                    }
                    return;
                }

                // Open delete modal
                const delBtn = e.target.closest('.open-delete-modal');
                if(delBtn){
                    console.debug('[leaves] clicked delete button', delBtn);
                    e.preventDefault();
                    const deleteModalEl = document.getElementById('deleteConfirmModal');
                    if(deleteModalEl){
                        const form = deleteModalEl.querySelector('form');
                        if(form){ form.setAttribute('action', delBtn.getAttribute('data-action')); console.debug('[leaves] set delete form action to', form.getAttribute('action')); }
                        const msg = deleteModalEl.querySelector('.modal_message'); if(msg) msg.textContent = `"${delBtn.getAttribute('data-title') || ''}" (${delBtn.getAttribute('data-start') || ''} → ${delBtn.getAttribute('data-end') || ''})`;

                        // Attach a direct click handler to the confirm button to ensure the action runs
                        const confirmBtnEl = document.getElementById('confirmDeleteBtn');
                        if(confirmBtnEl){
                            // overwrite previous handler to avoid duplicates
                            confirmBtnEl.onclick = function(ev){
                                ev.preventDefault();
                                try{ console.debug('[leaves] direct confirm delete clicked'); }catch(e){}
                                const formEl = deleteModalEl.querySelector('form'); if(!formEl) return; const actionUrl = formEl.getAttribute('action'); if(!actionUrl) return;
                                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                                fetch(actionUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                                .then(function(res){ if(res.ok) return res.json().catch(()=>({ success: true })); throw new Error('Network'); })
                                .then(function(json){ try{ const rowBtn = document.querySelector('[data-action="' + actionUrl + '"]'); const row = rowBtn ? rowBtn.closest('tr') : null; if(window.leavesTable && row) window.leavesTable.row(row).remove().draw(false); else if(row) row.remove(); }catch(e){ location.reload(); } try{ if(typeof bootstrap !== 'undefined'){ const m = bootstrap.Modal.getInstance(deleteModalEl); if(m) m.hide(); } else if (window.jQuery && typeof jQuery.fn.modal === 'function') { jQuery(deleteModalEl).modal('hide'); } else { manualHideModal(deleteModalEl); } }catch(e){} const alertBox = document.createElement('div'); alertBox.className = 'alert alert-success'; alertBox.textContent = (json && json.message) ? json.message : '{{ __('Leave request deleted') }}'; document.querySelector('.content.container-fluid').prepend(alertBox); setTimeout(()=> alertBox.remove(), 3000); })
                                .catch(function(err){ console.error(err); location.reload(); });
                            };
                        }

                            if(typeof bootstrap !== 'undefined'){ const modal = new bootstrap.Modal(deleteModalEl); modal.show(); } else if (window.jQuery && typeof jQuery.fn.modal === 'function') { jQuery(deleteModalEl).modal('show'); } else { try{ console.warn('[leaves] bootstrap not present, using manualShowModal fallback'); }catch(e){} manualShowModal(deleteModalEl); }
                    }
                    return;
                }

                // Confirm delete button inside modal
                const confirmDeleteBtn = e.target.closest('#confirmDeleteBtn');
                if(confirmDeleteBtn){
                    console.debug('[leaves] confirm delete clicked');
                    e.preventDefault();
                    const deleteModalEl = document.getElementById('deleteConfirmModal');
                    const form = deleteModalEl ? deleteModalEl.querySelector('form') : null; if(!form) return;
                    const action = form.getAttribute('action');
                    console.debug('[leaves] delete action', action);
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(action, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(function(res){ if(res.ok) return res.json().catch(()=>({ success: true })); throw new Error('Network'); })
                    .then(function(json){ try{ const rowBtn = document.querySelector('[data-action="' + action + '"]'); const row = rowBtn ? rowBtn.closest('tr') : null; if(window.leavesTable && row) window.leavesTable.row(row).remove().draw(false); else if(row) row.remove(); }catch(e){ location.reload(); } try{ if(typeof bootstrap !== 'undefined'){ const m = bootstrap.Modal.getInstance(deleteModalEl); if(m) m.hide(); } else if (window.jQuery && typeof jQuery.fn.modal === 'function') { jQuery(deleteModalEl).modal('hide'); } else { manualHideModal(deleteModalEl); } }catch(e){} const alertBox = document.createElement('div'); alertBox.className = 'alert alert-success'; alertBox.textContent = (json && json.message) ? json.message : '{{ __('Leave request deleted') }}'; document.querySelector('.content.container-fluid').prepend(alertBox); setTimeout(()=> alertBox.remove(), 3000); })
                    .catch(function(err){ console.error(err); location.reload(); });
                    return;
                }

                // Reject modal submit
                const rejectSubmitBtn = e.target.closest('#rejectSubmitBtn');
                if(rejectSubmitBtn){
                    console.debug('[leaves] reject submit clicked');
                    e.preventDefault();
                    const rejectModalEl = document.getElementById('rejectModal'); if(!rejectModalEl) return; const form = rejectModalEl.querySelector('form'); if(!form) return; const action = form.getAttribute('action'); console.debug('[leaves] reject action', action);
                    const commentEl = form.querySelector('textarea[name="comment"]'); const comment = commentEl ? commentEl.value : ''; const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''; const body = new URLSearchParams(); if(comment) body.append('comment', comment);
                    rejectSubmitBtn.disabled = true;
                    fetch(action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
                    .then(function(res){ if(res.ok) return res.json().catch(()=>({ success: true })); return res.text().then(function(txt){ throw new Error(txt || 'Network response was not ok.'); }); })
                    .then(function(json){ try{ const leaveId = form.querySelector('[name="leave_id"]') ? form.querySelector('[name="leave_id"]').value : null; const btn = document.querySelector('[data-leave-id="' + (leaveId || '') + '"]'); const row = btn ? btn.closest('tr') : null; if(row){ updateRowStatus(row, 'Rejected'); } }catch(e){} try{ if(typeof bootstrap !== 'undefined'){ const m = bootstrap.Modal.getInstance(rejectModalEl); if(m) m.hide(); } }catch(e){} const alertBox = document.createElement('div'); alertBox.className = 'alert alert-warning'; alertBox.textContent = (json && json.message) ? json.message : '{{ __('Leave rejected') }}'; document.querySelector('.content.container-fluid').prepend(alertBox); setTimeout(()=> alertBox.remove(), 3000); })
                    .catch(function(err){ console.error(err); const alertBox = document.createElement('div'); alertBox.className = 'alert alert-danger'; alertBox.textContent = err && err.message ? err.message : '{{ __('An error occurred while rejecting the leave') }}'; document.querySelector('.content.container-fluid').prepend(alertBox); setTimeout(()=> alertBox.remove(), 5000); })
                    .finally(function(){ rejectSubmitBtn.disabled = false; });
                    return;
                }
            });

        });
    })();
</script>
<script>
    // Inline fallback: ensure loader is hidden when the general modal receives content or is shown.
    (function(){
        function hideLoader(){
            try{
                var el = document.getElementById('loader-wrapper');
                if(!el) return;
                el.style.display = 'none';
                el.classList.remove('d-block');
                if(window.jQuery) try{ jQuery(el).hide(); }catch(e){}
            }catch(e){console.error('hideLoader failed', e)}
        }

        // If Bootstrap shows the modal, hide loader immediately
        try{
            document.addEventListener('shown.bs.modal', function(ev){
                if(ev && ev.target && ev.target.id === 'generalModalPopup'){
                    hideLoader();
                }
            });
        }catch(e){/* ignore */}

        // MutationObserver fallback: when modal body gets content, hide loader
        try{
            var target = document.querySelector('#generalModalPopup .body');
            if(target){
                var obs = new MutationObserver(function(){ hideLoader(); });
                obs.observe(target, { childList: true, subtree: true });
                setTimeout(function(){ try{ obs.disconnect(); }catch(e){} }, 5000);
            }
        }catch(e){/* ignore */}

        // When user clicks the Add button, ensure loader won't remain (safety net)
        document.addEventListener('click', function(e){
            var btn = e.target.closest('[data-ajax-modal="true"]');
            if(btn){
                // hide after a short delay in case other scripts re-show it briefly
                setTimeout(hideLoader, 300);
                setTimeout(hideLoader, 1200);
            }
        });
    })();
</script>
@endpush

@push('page-styles')
<style>
    /* Pixel-tuned three-dot action button to match Tickets UI */
    .no-caret {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        line-height: 1;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: #6c757d;
        transition: color .12s ease, transform .06s ease;
    }
    /* remove the default bootstrap caret */
    .no-caret::after { display: none !important; }
    .no-caret i { font-size: 14px; }
    .no-caret:hover, .no-caret:focus {
        background: transparent;
        color: #495057;
        transform: translateY(-1px);
        outline: none;
    }

    /* Dropdown menu visual polish */
    .dropdown-menu { 
        min-width: 180px; 
        border-radius: 8px; 
        padding: .25rem 0; 
        box-shadow: 0 8px 28px rgba(0,0,0,0.08);
    }
    .dropdown-menu .dropdown-item { 
        display: flex; 
        align-items: center; 
        gap: .5rem; 
        padding: .5rem .9rem; 
        color: #343a40;
    }
    .dropdown-menu .dropdown-item i { 
        width: 18px; 
        text-align: center; 
    }
    .dropdown-menu .dropdown-item:hover { 
        background: #f8f9fa; 
    }

    /* Slight adjustment so actions column keeps width and alignment */
    table#leaves-table td.text-end { vertical-align: middle; }

    /* Custom confirm modal styling to match example */
    .custom-confirm-modal .modal-dialog { max-width: 460px; }
    .custom-confirm-modal .modal-content {
        border-radius: 10px;
        padding: 0;
        box-shadow: 0 18px 40px rgba(0,0,0,0.18);
        border: none;
        overflow: hidden;
    }
    .custom-confirm-modal .modal-body { background: #ffffff; }
    .btn-pill { border-radius: 30px; padding: .5rem 1.4rem; font-weight: 600; }
    .btn-accent { background: transparent; color: #f6a95b; border: 2px solid #f6a95b; }
    .btn-accent:hover { background: #f6a95b; color: #fff; }
    .btn-outline-accent { background: transparent; color: #f6a95b; border: 2px solid rgba(246,169,91,0.15); }
    .btn-outline-accent:hover { background: rgba(246,169,91,0.06); }
    .custom-confirm-modal .modal-title { font-size: 18px; font-weight: 600; }
    .custom-confirm-modal p.modal_message, .custom-confirm-modal p.text-muted { margin-bottom: 0; }
</style>
@endpush
