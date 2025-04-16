<?php

namespace App\Filament\Admin\Resources\JenisPengajuanResource\Pages;

use App\Filament\Admin\Resources\JenisPengajuanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Models\JenisPengajuan;
use Illuminate\Database\Eloquent\Model;
use App\Models\FormField;
use App\Models\JensiPengajuan;

class CreateJenisPengajuan extends CreateRecord
{
    protected static string $resource = JenisPengajuanResource::class;
     protected function mutateFormDataBeforeCreate(array $data): array
    {   
        
        if (isset($data['form_fields'][0])) {
            $data['jenis_pengajuan_id'] = self::generatePengajuanJenisId();
        }
        return $data;
    }  
    protected function handleRecordCreation(array $data):Model
    {
        $record = static::getModel()::create($data);
        if (isset($data['form_fields'])) {
            foreach ($data['form_fields'] as $field) {
                $record->formfield()->create([
                    'jenis_pengajuan_id' => $record->jenis_pengajuan_id,
                    'field_name' => $field['field_name'],
                ]);
            }
        }
        return $record;
    }
    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Create')
            ->action(fn (CreateRecord $livewire) => $livewire->create())
            ->requiresConfirmation()
            ->modalHeading('Confirm the Order')
            ->modalDescription('Do you confirm that the order is correct?')
            ->modalDescription('')
            ->modalSubmitActionLabel('Confirm')
            ->keyBindings(['mod+s']);
    }
    public static function generatePengajuanJenisId()
    {
        // Get the last inserted pengajuan_id (sort by the last created record)
         $lastPengajuan = JenisPengajuan::withTrashed()  // Include soft-deleted records
        ->latest('created_at')
        ->first();

        // Check if there is an existing record
        if ($lastPengajuan) {
            // Extract the numeric part from the last ID and increment it
            preg_match('/(\d+)$/', $lastPengajuan->jenis_pengajuan_id, $matches);
            $lastNumber = $matches[0] ?? 0;
            $nextNumber = (int)$lastNumber + 1;
        } else {
            // Start with 1 if no records exist
            $nextNumber = 1;
        }
        // Format the new ID (e.g., PGA-0001, PGA-0002, etc.)
        return 'JP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
