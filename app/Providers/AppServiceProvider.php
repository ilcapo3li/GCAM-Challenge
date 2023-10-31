<?php

namespace App\Providers;

use App\Models\BoardItem;
use App\Models\InviteUser;
use App\Observers\BoardItemObserver;
use App\Observers\InviteUserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        BoardItem::observe(BoardItemObserver::class);
        InviteUser::observe(InviteUserObserver::class);
    }
}
