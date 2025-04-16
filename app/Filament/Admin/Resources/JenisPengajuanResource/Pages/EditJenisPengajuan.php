<?php

namespace App\Filament\Admin\Resources\JenisPengajuanResource\Pages;

use App\Filament\Admin\Resources\JenisPengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJenisPengajuan extends EditRecord
{
    protected static string $resource = JenisPengajuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
