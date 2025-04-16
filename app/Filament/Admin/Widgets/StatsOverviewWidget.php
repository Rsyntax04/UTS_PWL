<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use App\Models\Role;
use App\Models\Prodi;
use App\Models\JenisPengajuan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Filament\Admin\Resources\UserResource;
use App\Filament\Admin\Resources\RoleResource;
use App\Filament\Admin\Resources\ProdiResource;
use App\Filament\Admin\Resources\JenisPengajuanResource;
class StatsOverviewWidget extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Users', User::count())
                ->description('Total registered users')
                ->icon('heroicon-o-users')
                ->color('success')
                ->url(UserResource::getUrl('index')),

            Card::make('Total Prodi', Prodi::count())
                ->description('Programs study available')
                ->icon('heroicon-o-academic-cap')
                ->color('warning')
                ->url(ProdiResource::getUrl('index')),

            Card::make('Total Jenis Pengajuan', JenisPengajuan::count())
                ->description('Types of Submissions')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->url(JenisPengajuanResource::getUrl('index')),
        ];
    }
}

