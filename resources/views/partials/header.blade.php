<div class="header">

    <!-- Logo -->
    <x-logo />
    <!-- /Logo -->

    <a id="toggle_btn" href="javascript:void(0);">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <!-- Header Title -->
    <div class="page-title-box">
        <h3>{{ Theme('name') ?? config('app.name') }}</h3>
    </div>
    <!-- /Header Title -->

    <a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa-solid fa-bars"></i></a>

    <!-- Header Menu -->
    <ul class="nav user-menu">
        {{-- Clock Status Indicator for Employees --}}
        @auth
        @if(auth()->user()->type === \App\Enums\UserType::EMPLOYEE)
        @php
            $tz = LocaleSettings('timezone') ?? config('app.timezone');
            $start = \Carbon\Carbon::now($tz)->startOfDay()->setTimezone('UTC');
            $end = \Carbon\Carbon::now($tz)->endOfDay()->setTimezone('UTC');
            $todayClockin = \App\Models\Attendance::where('user_id', auth()->id())
                ->whereBetween('created_at', [$start, $end])
                ->first();
            $isClockedIn = false;
            $clockInTime = null;
            
            if(!empty($todayClockin)){
                $latestClockin = $todayClockin->timestamps()->latest()->whereNull('endTime')->first();
                if(!empty($latestClockin)){
                    $isClockedIn = true;
                    $clockInTime = \Carbon\Carbon::parse($latestClockin->startTime, 'UTC')->setTimezone($tz);
                }
            }
        @endphp
        <li class="nav-item dropdown">
            <a href="{{ route('attendances.index') }}" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Attendance Status">
                @if($isClockedIn)
                    <i class="fa fa-clock text-success fa-lg"></i>
                @else
                    <i class="fa fa-clock text-secondary fa-lg"></i>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 280px;">
                <div class="text-center">
                    @if($isClockedIn)
                        <div class="mb-3">
                            <span class="badge bg-success d-flex align-items-center justify-content-center gap-2 p-3" style="font-size: 14px;">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                                <span>Currently Clocked In</span>
                            </span>
                        </div>
                        <p class="mb-2"><strong>Since:</strong> {{ $clockInTime->format('g:i A') }}</p>
                        <p class="mb-3 text-muted small">{{ $clockInTime->diffForHumans() }}</p>
                        <a href="{{ route('attendances.index') }}" class="btn btn-sm btn-primary w-100">
                            <i class="fa fa-clock"></i> View Attendance
                        </a>
                    @else
                        <div class="mb-3">
                            <i class="fa fa-clock fa-3x text-muted mb-2"></i>
                            <p class="mb-0"><strong>Not Clocked In</strong></p>
                            <p class="text-muted small">You haven't clocked in today</p>
                        </div>
                        <a href="{{ route('attendances.index') }}" class="btn btn-sm btn-success w-100">
                            <i class="fa fa-sign-in-alt"></i> Clock In Now
                        </a>
                    @endif
                </div>
            </div>
        </li>
        @endif
        @endauth
        
        {{-- Notifications (payslips, system alerts) dropdown --}}
        @auth
        @php
            $unreadNotifications = collect();
            $totalNotif = 0;
            try {
                if(\Illuminate\Support\Facades\Schema::hasTable('notifications')){
                    $unreadNotifications = auth()->user()->unreadNotifications()->take(10)->get();
                    $totalNotif = auth()->user()->unreadNotifications()->count();
                }
            } catch(\Throwable $e){
                // If the notifications table doesn't exist or DB is unreachable, silently fallback
            }
        @endphp

        <li class="nav-item dropdown">
            <a href="#" class="nav-link" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-bell fa-lg"></i>
                @if($totalNotif > 0)
                    <span class="badge rounded-pill bg-danger ms-1">{{ $totalNotif }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notifDropdown" style="min-width:320px;">
                <div class="dropdown-header px-3 py-2">
                    <strong>{{ __('Notifications') }}</strong>
                    <span class="small text-muted float-end">{{ $totalNotif }} {{ __('unread') }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($unreadNotifications as $n)
                        <a href="{{ route('notifications.read', ['id' => $n->id]) }}" class="list-group-item list-group-item-action d-flex align-items-start">
                            <div class="me-2"><i class="fa-solid fa-file-invoice-dollar fa-2x"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $n->data['message'] ?? __('New notification') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="list-group-item text-center small text-muted">{{ __('No new notifications') }}</div>
                    @endforelse
                </div>
            </div>
        </li>
    </li>

    @endauth

    {{-- Messaging dropdown: per-user unread badges --}}
        @auth
        @php
            // chat_messages uses 'user_id' as the sender column in this app
            $unreadGroups = \App\Models\ChatMessage::select('user_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
                                ->where('receiver_id', auth()->id())
                                ->where('is_read', false)
                                ->groupBy('user_id')
                                ->get();
            $totalUnread = $unreadGroups->sum('cnt');
            $senderIds = $unreadGroups->pluck('user_id')->toArray();
            $senders = \App\Models\User::whereIn('id', $senderIds)->get()->keyBy('id');
        @endphp

        <li class="nav-item dropdown">
            <a href="#" class="nav-link" id="messagesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-message fa-lg"></i>
                @if($totalUnread > 0)
                    <span class="badge rounded-pill bg-danger ms-1">{{ $totalUnread }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="messagesDropdown" style="min-width:300px;">
                <div class="dropdown-header px-3 py-2">
                    <strong>{{ __('Messages') }}</strong>
                    <span class="small text-muted float-end">{{ $totalUnread }} {{ __('unread') }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($unreadGroups as $group)
                        @php $sender = $senders->get($group->user_id); @endphp
                        @if($sender)
                        <a href="{{ route('app.chat') . '?contact=' . Crypt::encrypt($sender->id) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                            <img src="{{ !empty($sender->avatar) ? asset('storage/users/'.$sender->avatar) : asset('images/user.jpg') }}" class="rounded-circle me-2" width="36" height="36" alt="">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $sender->fullname }}</div>
                                <div class="small text-muted">{{ $group->cnt }} {{ __('new messages') }}</div>
                            </div>
                            <span class="badge rounded-pill bg-danger ms-2">{{ $group->cnt }}</span>
                        </a>
                        @endif
                    @empty
                        <div class="list-group-item text-center small text-muted">{{ __('No new messages') }}</div>
                    @endforelse
                </div>
            </div>
        </li>

        @endauth


        <li class="nav-item dropdown has-arrow main-drop">
            <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                <span class="user-img"><img src="{{ !empty(auth()->user()->avatar) ? uploadedAsset(auth()->user()->avatar,'users'): asset('images/user.jpg') }}" alt="User Image">
                    <span class="status online"></span></span>
                <span>{{ auth()->user()->fullname }}</span>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('profile') }}">{{ __('My Profile') }}</a>
                <a onclick="document.getElementById('logout_user_form').submit()" class="dropdown-item logout_btn" href="javascript:void(0);">Logout</a>
            </div>
        </li>
    </ul>
    <!-- /Header Menu -->

    <!-- Mobile Menu -->
    <div class="dropdown mobile-user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i
                class="fa-solid fa-ellipsis-vertical"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="profile.html">My Profile</a>
            <a onclick="document.getElementById('logout_user_form').submit()" class="dropdown-item logout_btn" href="javascript:void(0);">Logout</a>
        </div>
    </div>
    <!-- /Mobile Menu -->
    <form action="{{ route('logout') }}" id="logout_user_form" method="post">@csrf</form>

</div>
