<div class="modal-inner-card p-3">
    <style>
        /* Scoped form theming: prefer global CSS variables when available */
        .modal-inner-card {
            background: var(--card-bg, var(--bs-body-bg, #fff));
            color: var(--bs-body-color, #222);
        }

        .modal-inner-card .form-label.small.text-muted {
            color: var(--muted, rgba(107,114,128,1));
        }

        /* Make the accent buttons use the global --accent variable if defined */
        .modal-inner-card .btn-accent {
            background: var(--accent, #f6a95b) !important;
            color: #fff !important;
            border-color: transparent !important;
        }

        .modal-inner-card .btn-outline-accent {
            background: transparent !important;
            color: var(--accent, #f6a95b) !important;
            border-color: rgba(0,0,0,0.06) !important;
        }

        /* Inputs should match app form styling */
        .modal-inner-card .form-control {
            background: var(--input-bg, #fff);
            color: var(--bs-body-color, #222);
            border-color: var(--input-border, rgba(0,0,0,0.08));
        }

        /* Small touch for the token display so it doesn't stand out */
        .modal-inner-card #tokens_left_display {
            background: transparent; /* keep readonly subtle */
            color: var(--bs-body-color, #222);
        }

        /* Readonly / uneditable inputs should look visually different so users know they can't change them */
        .modal-inner-card .form-control[readonly],
        .modal-inner-card input[readonly],
        .modal-inner-card textarea[readonly],
        .modal-inner-card .form-control[disabled],
        .modal-inner-card select[disabled] {
            background-color: var(--bs-light, #f8f9fa) !important;
            color: var(--muted, rgba(107,114,128,1)) !important;
            border-color: var(--input-border, rgba(0,0,0,0.06)) !important;
            box-shadow: none !important;
            cursor: not-allowed !important;
            opacity: 1 !important; /* keep readable */
        }
    </style>
    {{-- Title already rendered by the page wrapper/breadcrumb. Avoid duplicate heading here. --}}
    <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="row gx-3">
            <div class="col-md-6 mb-3">
                <label class="form-label small text-muted">{{ __('Date Filed') }}</label>
                <input type="date" name="date_filed" class="form-control form-control-sm" readonly value="{{ old('date_filed', now()->toDateString()) }}" aria-label="Date filed">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small text-muted">{{ __('Leave Type') }}</label>
                <select name="leave_type_id" class="form-control form-control-sm" aria-label="Leave type">
                    @if(count($types ?? []) > 0)
                        <option value="" disabled {{ old('leave_type_id') ? '' : 'selected' }}>{{ __('Select leave type') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    @else
                        <option value="" disabled selected>{{ __('No leave types configured') }}</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="row gx-3">
            <div class="col-md-6 mb-3">
                <label class="form-label small text-muted">{{ __('Start Date') }}</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="start_date" class="form-control form-control-sm" required aria-required="true" aria-label="Start date" />
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small text-muted">{{ __('End Date') }}</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="end_date" class="form-control form-control-sm" required aria-required="true" aria-label="End date" />
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>

        <div class="row gx-3">
            <div class="col-md-6 mb-3">
                <label class="form-label small text-muted">{{ __('Type of Day') }}</label>
                <select name="day_type" id="day_type" class="form-control form-control-sm" aria-label="Type of day">
                    <option value="full" {{ old('day_type') == 'full' ? 'selected' : '' }}>{{ __('Whole Day') }}</option>
                    <option value="half" {{ old('day_type') == 'half' ? 'selected' : '' }}>{{ __('Half Day') }}</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small text-muted">{{ __('Total Days of Leave') }}</label>
                <input type="text" id="total_days_display" class="form-control form-control-sm" readonly value="{{ old('total_days', 0) }}" aria-label="Total days">
                <input type="hidden" name="total_days" id="total_days" value="{{ old('total_days', 0) }}">
            </div>
        </div>

        <div class="row gx-3">
            <div class="col-md-6 mb-3">
                <label class="form-label small text-muted">{{ __('Credits left') }}</label>
                <?php
                    $leaveToken = auth()->user()->leaveToken;
                    $initialTokens = $leaveToken->tokens ?? 0;
                ?>
                <input type="text" id="tokens_left_display" class="form-control form-control-sm" readonly value="{{ $initialTokens }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted">{{ __('Reason') }}</label>
            <textarea name="reason" class="form-control form-control-sm" rows="3" placeholder="{{ __('Briefly describe the reason for your leave') }}">{{ old('reason') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted">{{ __('Attachments') }}</label>
            <input type="file" name="attachments[]" multiple class="form-control form-control-sm" aria-label="Attachments" />
            <div class="form-text small">{{ __('Optional — PDFs or images, max 5MB each') }}</div>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-secondary btn-sm me-2" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button class="btn btn-primary btn-pill btn-sm" type="submit">{{ __('Submit') }}</button>
        </div>
    </form>
</div>

<script>
    (function(){
        // Query within the current fragment to avoid interfering with other forms on the page
        const root = document.currentScript ? document.currentScript.parentNode : document;
        const startEl = root.querySelector('input[name="start_date"]');
        const endEl = root.querySelector('input[name="end_date"]');
        const dayTypeEl = root.querySelector('#day_type');
        const totalDisplay = root.querySelector('#total_days_display');
        const totalHidden = root.querySelector('#total_days');

        function parseDate(v){
            if(!v) return null;
            // accept YYYY-MM-DD or DD/MM/YYYY (common picker formats)
            if(v.indexOf('-') !== -1){
                const parts = v.split('-');
                if(parts.length !== 3) return null;
                return new Date(parts[0], parts[1]-1, parts[2]);
            }
            if(v.indexOf('/') !== -1){
                const parts = v.split('/');
                if(parts.length !== 3) return null;
                // assume DD/MM/YYYY
                return new Date(parts[2], parts[1]-1, parts[0]);
            }
            // fallback: try Date constructor
            const d = new Date(v);
            return isNaN(d.getTime()) ? null : d;
        }

        function compute(){
            const s = startEl ? parseDate(startEl.value) : null;
            const e = endEl ? parseDate(endEl.value) : null;
            if(!s || !e){
                if(totalDisplay) totalDisplay.value = 0;
                if(totalHidden) totalHidden.value = 0;
                return;
            }
            const diff = Math.floor((e - s) / (1000*60*60*24));
            let days = diff >= 0 ? (diff + 1) : 0;
            const type = dayTypeEl ? dayTypeEl.value : 'full';
            const total = type === 'half' ? (days * 0.5) : days;
            if(totalDisplay) totalDisplay.value = total;
            if(totalHidden) totalHidden.value = total;
        }

        [startEl,endEl].forEach(el => el && el.addEventListener('change', compute));
        if(dayTypeEl) dayTypeEl.addEventListener('change', compute);
        compute();

        // Before submit, normalize date inputs to YYYY-MM-DD so server receives a consistent format
        const form = root.querySelector('form');
        if(form){
            form.addEventListener('submit', function(e){
                try{
                    if(startEl && startEl.value){
                        const s = parseDate(startEl.value);
                        if(s){
                            // yyyy-mm-dd
                            startEl.value = s.getFullYear() + '-' + String(s.getMonth()+1).padStart(2,'0') + '-' + String(s.getDate()).padStart(2,'0');
                        }
                    }
                    if(endEl && endEl.value){
                        const d = parseDate(endEl.value);
                        if(d){
                            endEl.value = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                        }
                    }
                }catch(err){
                    console.error('date normalization failed', err);
                }
            });
        }
    })();
</script>

<script>
    (function(){
        const root = document.currentScript ? document.currentScript.parentNode : document;
        const tokensEl = root.querySelector('#tokens_left_display');
        const userSelect = root.querySelector('select[name="user_id"]');

        async function refreshTokensFor(userId){
            if(!tokensEl) return;
            try{
                if(!userId){
                    // fallback to current user's tokens already set in blade
                    return;
                }
                const resp = await fetch(`/settings/users/${userId}/leave-tokens`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if(!resp.ok){
                    tokensEl.value = 'N/A';
                    return;
                }
                const data = await resp.json();
                if(typeof data.tokens === 'number'){
                    tokensEl.value = data.tokens;
                } else {
                    tokensEl.value = 'N/A';
                }
            }catch(err){
                console.error('fetch tokens failed', err);
                if(tokensEl) tokensEl.value = 'N/A';
            }
        }

        if(userSelect){
            userSelect.addEventListener('change', function(e){
                const id = this.value;
                refreshTokensFor(id);
            });
            // initial load if a value is selected
            if(userSelect.value) refreshTokensFor(userSelect.value);
        }
    })();
</script>
