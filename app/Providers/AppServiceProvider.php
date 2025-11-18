<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Models\Ticket;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LaravelLang\Routes\Events\LocaleHasBeenSetEvent;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // register console commands (including attendance normalization)
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\NormalizeAttendanceTimestamps::class,
                \App\Console\Commands\ClearPayslips::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register ticket policy
        Gate::policy(Ticket::class, TicketPolicy::class);
        // Register leave policy
        if (class_exists(\App\Models\Leave::class)) {
            Gate::policy(\App\Models\Leave::class, \App\Policies\LeavePolicy::class);
        }

        Gate::before(function ($user, $ability) {
            // Grant all permissions to System Admin and Super Admin
            if ($user->hasRole('System Admin') || $user->hasRole('Super Admin')) {
                return true;
            }
            // Grant all permissions to users with SYSTEM_ADMIN type
            if ($user->type === \App\Enums\UserType::SYSTEM_ADMIN) {
                return true;
            }
            return null;
        });
        
        // Blade directives for user types
        \Illuminate\Support\Facades\Blade::directive('systemadmin', function () {
            return "<?php if(auth()->check() && auth()->user()->type === \App\Enums\UserType::SYSTEM_ADMIN): ?>";
        });
        
        \Illuminate\Support\Facades\Blade::directive('endsystemadmin', function () {
            return "<?php endif; ?>";
        });
        
        \Illuminate\Support\Facades\Blade::directive('superadmin', function () {
            return "<?php if(auth()->check() && auth()->user()->type === \App\Enums\UserType::SUPERADMIN): ?>";
        });
        
        \Illuminate\Support\Facades\Blade::directive('endsuperadmin', function () {
            return "<?php endif; ?>";
        });
        
        Event::listen(static function (LocaleHasBeenSetEvent $event) {
            $lang = $event->locale->code;
            Log::info('Locale set to: ' . $lang);
        });
    }
}
