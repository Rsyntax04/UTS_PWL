<?php

namespace App\Observers;

use App\Models\Pengajuan;
use Filament\Notifications\Notification;
use App\Models\jenisPengajuan;
use App\Models\User;
use App\Notifications\PengajuanCreatedNotification;
use Illuminate\Support\Facades\Notification as LaravelNotification;
class PengajuanObserver
{
    /**
     * Handle the Pengajuan "created" event.
     */
    public function created(Pengajuan $pengajuan): void
    {
        // $surat = jenisPengajuan::where('jenis_pengajuan_id',$pengajuan['jenis_pengajuan_id'])->first();
        // $user = User::where('nrp', $pengajuan['nrp'])->first();
        // $kaprodiUsers = User::role('kaprodi')->where('prodi_id', $user->prodi_id)->get();
        Notification::make()
            ->title('DB Test')
            ->body('This should now be saved')
            ->success()
            ->sendToDatabase(User::first());
                        

    }

    /**
     * Handle the Pengajuan "updated" event.
     */
    public function updated(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "deleted" event.
     */
    public function deleted(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "restored" event.
     */
    public function restored(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "force deleted" event.
     */
    public function forceDeleted(Pengajuan $pengajuan): void
    {
        //
    }
}
