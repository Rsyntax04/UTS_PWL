<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PengajuanCreatedNotification extends Notification
{
    use Queueable;

    protected $pengajuan;

    public function __construct($pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function via($notifiable)
    {
        return ['database']; // Send notification via database channel
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => "A new Pengajuan with ID {$this->pengajuan->id} has been created!",
        ];
    }
}
