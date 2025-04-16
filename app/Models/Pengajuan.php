<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

#[observedBy([PengajuanObserver::classf])]
class Pengajuan extends Model
{
    use Notifiable;
    // use SoftDeletes;
    protected $table = 'pengajuan';
    protected $primaryKey = 'pengajuan_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'pengajuan_id',
        'nrp',
        'jenis_pengajuan_id',
        'file_hasil_pengajuan',
        'status',
    ];
     public function jenisPengajuan()
    {
        return $this->belongsTo(JenisPengajuan::class, 'jenis_pengajuan_id');
    }

    public function metadata()
    {
        return $this->hasMany(PengajuanMetadata::class, 'pengajuan_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'nrp'); // Adjust based on your actual foreign key
    }
}
