<div>
    <div class="row">
        <div class="col-md-4">
            <div class="card punch-status">
                <div class="card-body">
                    <h5 class="card-title">
                        <div class="row">
                            <div class="col-9">
                                {{ __('Timesheet') }} <small class="text-muted">{{ format_date($currentDateString) }}</small>
                            </div>
                            <div class="col-3">
                                <div class="d-flex" x-data="{
                                    h: {{ $clockHour }},
                                    m: {{ $clockMinute }},
                                    s: {{ $clockSecond }},
                                    format(n){ return n < 10 ? '0'+n : n },
                                    updateClock(){
                                        const now = new Date();
                                        this.h = now.getHours();
                                        this.m = now.getMinutes();
                                        this.s = now.getSeconds();
                                        const span = document.getElementById('spanTimer');
                                        span.textContent = this.format(this.h)+':'+this.format(this.m)+':'+this.format(this.s);
                                    },
                                    init(){
                                        this.updateClock();
                                        setInterval(()=>{
                                            this.updateClock();
                                        }, 1000);
                                    }
                                }">
                                    <span id="spanTimer"></span>
                                </div>
                            </div>
                        </div>
                    </h5>
                    
                    <div class="punch-info">
                        <div class="punch-hours">
                            @php
                                $hours = floor($totalHoursFloat);
                                $minutes = floor(($totalHoursFloat - $hours) * 60);
                                $seconds = floor(((($totalHoursFloat - $hours) * 60) - $minutes) * 60);
                            @endphp
                            <span id="elapsedTimer" 
                                  x-data="{
                                      clockedIn: {{ !empty($clockedIn) ? 'true' : 'false' }},
                                      startTime: '{{ $timeStarted ?? '' }}',
                                      h: {{ $hours }},
                                      m: {{ $minutes }},
                                      s: {{ $seconds }},
                                      format(n){ return n < 10 ? '0'+n : n },
                                      updateElapsed(){
                                          if(!this.clockedIn || !this.startTime) return;
                                          const now = new Date();
                                          const start = new Date(this.startTime);
                                          const diffMs = now - start;
                                          const diffSecs = Math.floor(diffMs / 1000);
                                          this.h = Math.floor(diffSecs / 3600);
                                          this.m = Math.floor((diffSecs % 3600) / 60);
                                          this.s = diffSecs % 60;
                                          document.getElementById('elapsedTimer').textContent = this.format(this.h)+':'+this.format(this.m)+':'+this.format(this.s);
                                      },
                                      init(){
                                          this.updateElapsed();
                                          if(this.clockedIn){
                                              setInterval(()=>{ this.updateElapsed(); }, 1000);
                                          }
                                      }
                                  }"
                            >{{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}</span>
                        </div>
                    </div>
                    <div class="punch-btn-section">
                        @if (!empty($clockedIn) && !empty($timeId))
                        <button type="button" wire:click="clockout('{{ $timeId }}')" class="btn btn-primary punch-btn">{{ __('Clock Out') }}</button>
                        @else
                        <button type="button" wire:click="clockin" class="btn btn-primary punch-btn">{{ __('Clock In') }}</button>
                        @endif
                    </div>
                    <div class="statistics">
                        <div class="row">
                            @if (!empty($clockedIn) && !empty($timeStarted))
                            <div class="col-md-12 text-center">
                                <div class="stats-box">
                                    <p>{{ __('Started At') }}</p>
                                    <h6>{{ format_date($timeStarted,'H:i:s A') }}</h6>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card att-statistics">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Statistics') }}</h5>
                    <div class="stats-list">
                        <div class="stats-info">
                            <p>{{ __('Today') }} <strong><small> {{ number_format($totalHoursToday, 2) }} {{ \Str::plural(__('Hour'),$totalHoursToday) }}</small></strong></p>
                            <div class="progress">
                                <div class="progress-bar bg-primary w-{{ $totalHoursToday /100 }}" role="progressbar" aria-valuenow="{{ $totalHoursToday/100 }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="stats-info">
                            <p>{{ __('This Week ') }}<strong> <small> {{ number_format($totalHoursThisWeek, 2) }} {{ \Str::plural(__('Hour'),$totalHoursThisWeek) }}</small></strong></p>
                            <div class="progress">
                                <div class="progress-bar bg-warning w-{{ $totalHoursThisWeek/100 }}" role="progressbar" aria-valuenow="{{ $totalHoursThisWeek/100 }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="stats-info">
                            <p>{{ __('This Month') }} <strong> <small> {{ number_format($totalHoursThisMonth, 2) }} {{ \Str::plural(__('Hour'),$totalHoursToday) }}</small></strong></p>
                            <div class="progress">
                                <div class="progress-bar bg-success w-{{ $totalHoursThisMonth /100 }}" role="progressbar" aria-valuenow="{{ $totalHoursThisMonth /100 }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        
                        @if($totalOvertimeToday > 0)
                        <div class="stats-info">
                            <p>{{ __('Overtime Today') }} <strong> <small class="text-success">+{{ number_format($totalOvertimeToday, 2) }} {{ \Str::plural(__('Hour'),$totalOvertimeToday) }}</small></strong></p>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 100%" role="progressbar"></div>
                            </div>
                        </div>
                        @endif
                        
                        @if($totalUndertimeToday > 0)
                        <div class="stats-info">
                            <p>{{ __('Undertime Today') }} <strong> <small class="text-warning">-{{ number_format($totalUndertimeToday, 2) }} {{ \Str::plural(__('Hour'),$totalUndertimeToday) }}</small></strong></p>
                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width: 100%" role="progressbar"></div>
                            </div>
                        </div>
                        @endif
                        
                        @if($hoursRemainingToday > 0)
                        <div class="stats-info">
                            <p>{{ __('Time Remaining for 8hrs') }} <strong> <small class="text-info">{{ number_format($hoursRemainingToday, 2) }} {{ \Str::plural(__('Hour'),$hoursRemainingToday) }}</small></strong></p>
                            <div class="progress">
                                <div class="progress-bar bg-info w-100" role="progressbar"></div>
                            </div>
                        </div>
                        @endif
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card recent-activity">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Today Activity') }}</h5>
                    <ul class="res-activity-list">
                        @if (!empty($todayActivity))
                            @foreach ($todayActivity as $item)
                            <li>
                                <p class="mb-0">
                                    {{ __('Punch In at') }}
                                    @if($item->is_early || $item->is_late)
                                        {!! $item->status_badge !!}
                                    @endif
                                </p>
                                <p class="res-activity-time">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ !empty($item->startTime) ? \Illuminate\Support\Carbon::parse($item->startTime, 'UTC')->setTimezone($tz)->format('h:i A'): '' }}
                                </p>
                                @if($item->scheduled_start_time)
                                    <p class="mb-0"><small class="text-muted">Scheduled: {{ \Illuminate\Support\Carbon::parse($item->scheduled_start_time)->format('h:i A') }}</small></p>
                                @endif
                            </li>
                            @if (!empty($item->endTime))
                            <li>
                                <p class="mb-0">{{ __('Punch Out at') }}</p>
                                <p class="res-activity-time">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ !empty($item->endTime) ? \Illuminate\Support\Carbon::parse($item->endTime, 'UTC')->setTimezone($tz)->format('h:i A'): '' }}
                                </p>
                            </li>
                            <hr>
                            @endif
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table table-striped custom-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Date') }} </th>
                            <th>{{ __('Punch In') }}</th>
                            <th>{{ __('Punch Out') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Total Hours') }}</th>
                            <th>{{ __('OT/UT') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($attendances))
                            @foreach ($attendances as $i => $attendance)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ !empty($attendance->startTime) ? \Illuminate\Support\Carbon::parse($attendance->startTime, 'UTC')->setTimezone($tz)->format('d M Y') : \Illuminate\Support\Carbon::parse($attendance->created_at, 'UTC')->setTimezone($tz)->format('d M Y') }}</td>
                                <td>
                                    {{ !empty($attendance->startTime) ? \Illuminate\Support\Carbon::parse($attendance->startTime, 'UTC')->setTimezone($tz)->format('h:i A'): '' }}
                                    @if($attendance->scheduled_start_time)
                                        <br><small class="text-muted">Schedule: {{ \Illuminate\Support\Carbon::parse($attendance->scheduled_start_time)->format('h:i A') }}</small>
                                    @endif
                                </td>
                                <td>{{ !empty($attendance->endTime) ? \Illuminate\Support\Carbon::parse($attendance->endTime, 'UTC')->setTimezone($tz)->format('h:i A'): ''}}</td>
                                <td>
                                    {!! $attendance->status_badge !!}
                                    @if($attendance->time_difference_text)
                                        <br><small class="text-muted">{{ $attendance->time_difference_text }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-inverse-info">{{ $attendance->totalHours }}</span></td>
                                <td>
                                    @if($attendance->attendance)
                                        @if($attendance->attendance->overtime_hours > 0)
                                            <span class="badge bg-success" title="Overtime for this session">
                                                <i class="fa fa-clock"></i> +{{ number_format($attendance->attendance->overtime_hours, 2) }}h OT
                                            </span>
                                        @elseif($attendance->attendance->undertime_hours > 0)
                                            <span class="badge bg-warning" title="Undertime accumulated so far">
                                                <i class="fa fa-clock"></i> -{{ number_format($attendance->attendance->undertime_hours, 2) }}h UT
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ number_format($attendance->attendance->undertime_hours, 2) }}h left for 8hrs</small>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                            </tr>  
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal custom-modal fade" id="clockin_modal" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form wire:submit.prevent="clockin" method="post" enctype="multipart/form-data">
                @csrf
                <div x-data="{forProject: false}">
                    <x-form.input-block>
                    <div class="status-toggle">
                        <x-form.label>{{ __('For Project ?') }}</x-form.label>
                        <x-form.input type="checkbox" id="forProject" class="check" @click="forProject =! forProject" name="forProject" wire:model="forProject" />
                        <label for="forProject" class="checktoggle">checkbox</label>
                    </div>
                    </x-form.input-block>
                    <div x-show="forProject">
                        <x-form.input-block>
                            <x-form.label required>{{ __('Project') }}</x-form.label>
                            <select class="form-control" name="project" wire:model="project">
                                <option value="">{{ __('Select Project') }}</option>
                                @foreach (\Modules\Project\Models\Project::get() as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </x-form.input-block>
                    </div>
                </div>
                <div class="submit-section mb-3">
                    <x-form.button class="btn btn-primary submit-btn">{{ __('Start') }}</x-form.button>
                </div>
              </form>
            </div>
          </div>
        </div>
    </div>
      
    @script
    <script defer type="module">
        document.addEventListener('livewire:initialized', () => {
            Livewire.dispatch('refreshAttendance')
            Livewire.dispatch('fetchStatistics')
            Livewire.dispatch('IsClockedIn')
        })

        Livewire.on('Notification', (param) => {
            Toastify({
                text: param,
                className: "success",
            }).showToast()
        })
    </script>
    @endscript
</div>
