<?php

namespace App\Providers;

use App\Models\addSettingModel;
use App\Models\htmlResponce;
use App\Models\XhtmlResponce;
use App\Models\zHtmlResponce;
use App\Observers\addSettingObserver;
use App\Observers\htmlResponceObserver;
use App\Observers\xHtmlResponceObserver;
use App\Observers\zHtmlResponceObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        addSettingModel::observe(addSettingObserver::class);
        htmlResponce::observe(htmlResponceObserver::class);
        zHtmlResponce::observe(zHtmlResponceObserver::class);
        XhtmlResponce::observe(xHtmlResponceObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
