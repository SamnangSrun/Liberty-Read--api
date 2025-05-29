<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification;
use App\Policies\NotificationPolicy;



class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Notification::class => NotificationPolicy::class,
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register any application services here
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Bootstrap any services, such as setting up gates, policies, etc.
    }
}
