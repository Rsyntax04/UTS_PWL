<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanMetadata extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_metadata'; // Explicitly set the table name

    protected $fillable = ['pengajuan_id', 'form_field_id', 'field_value'];

    /**
     * Relationship: Each metadata entry belongs to a specific `pengajuan` submission.
     */
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}

