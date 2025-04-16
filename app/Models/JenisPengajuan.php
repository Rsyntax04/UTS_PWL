<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisPengajuan extends Model
{
    use softDeletes;
    protected $table = 'jenis_pengajuan';
    protected $primaryKey = 'jenis_pengajuan_id'; // Set the correct primary key
    public $incrementing = false; // Disable auto-increment if prodi_id is not an auto-incrementing integer
    protected $keyType = 'string'; // Define key type (use 'string' if it's UUID or VARCHAR)
    protected $fillable = [
        'jenis_pengajuan_id',
        'jenis_pengajuan_name',
    ];
    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class, 'jenis_pengajuan_id');
    }
    public function formfield()
    {
        return $this->hasMany(formfield::class, 'jenis_pengajuan_id');
    }
}
