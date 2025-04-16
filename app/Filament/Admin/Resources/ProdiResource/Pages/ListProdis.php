<?php

namespace App\Filament\Admin\Resources\ProdiResource\Pages;

use App\Filament\Admin\Resources\ProdiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdis extends ListRecords
{
    protected static string $resource = ProdiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
