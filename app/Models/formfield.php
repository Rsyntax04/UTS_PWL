<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\JenisPengajuan; // Ensure this is the correct namespace for JenisPengajuan

class formfield extends Model
{
    protected $table = 'form_field';
     public $timestamps = false;
    protected $primaryKey = 'id'; // Set the correct primary key
    protected $fillable = [
        'jenis_pengajuan_id',
        'field_name',
    ];
    public function jenisPengajuan()
    {
        return $this->belongsTo(JenisPengajuan::class, 'jenis_pengajuan_id');
    }
}
