<?php

namespace App\Filament\Mahasiswa\Resources\PengajuanResource\Pages;

use App\Filament\Mahasiswa\Resources\PengajuanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pengajuan;
use App\Models\PengajuanMetadata;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\formfield as FormField;
use Filament\Notifications\Notification;
use App\Models\jenisPengajuan;
use Filament\Notifications\Events\DatabaseNotificationsSent;
class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {   
        if (isset($data['metadata'])) {
            $metadata = $data['metadata'];
            $data['pengajuan_id'] = self::generatePengajuanId();
            $data['nrp'] = Auth::user()->nrp;
            $data['status'] = 'pending';
        }
        return $data;
    }  
     protected function handleRecordCreation(array $data):Model
    {
        $record =  static::getModel()::create($data);
        // If metadata exists, save it related to this Pengajuan
        if (isset($data['metadata'])) {
            foreach ($data['metadata'] as $field => $value) {
                $form_field_id = FormField::where('jenis_pengajuan_id',$data['jenis_pengajuan_id'])
                ->where('field_name', $field)
                ->value('id');
                $record->metadata()->create([
                    'pengajuan_id' => $record->pengajuan_id, // Use the generated ID
                    'form_field_id' => $form_field_id,
                    'field_value' => $value,
                ]);
            }
        }
            
        return $record;
    } 
    
    public static function generatePengajuanId()
    {
        // Get the last inserted pengajuan_id (sort by the last created record)
        $lastPengajuan = Pengajuan::latest('created_at')->first();

        // Check if there is an existing record
        if ($lastPengajuan) {
            // Extract the numeric part from the last ID and increment it
            preg_match('/(\d+)$/', $lastPengajuan->pengajuan_id, $matches);
            $lastNumber = $matches[0] ?? 0;
            $nextNumber = (int)$lastNumber + 1;
        } else {
            // Start with 1 if no records exist
            $nextNumber = 1;
        }
        // Format the new ID (e.g., PGA-0001, PGA-0002, etc.)
        return 'PGA-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
