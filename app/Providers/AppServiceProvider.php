<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Models\Pengajuan;
use App\Observers\PengajuanObserver;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Models\DatabaseNotification;
class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        \Filament\Http\Responses\Auth\Contracts\LoginResponse::class => \App\Http\Responses\LoginResponse::class,
        \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class => \App\Http\Responses\LogoutResponse::class,
    ];
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
        Pengajuan::observe(PengajuanObserver::class);
        Livewire::component('role-delete-modal', \App\Http\Livewire\RoleDeleteModal::class);
    }
}

