<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prodi extends Model
{
    use SoftDeletes;
    protected $table = 'prodis';
    protected $primaryKey = 'prodi_id'; // Set the correct primary key
    public $incrementing = false; // Disable auto-increment if prodi_id is not an auto-incrementing integer
    protected $keyType = 'int'; // Define key type (use 'string' if it's UUID or VARCHAR)
    protected $fillable = [
        'prodi_id',
        'prodi_name',
    ];
    public function users()
    {
        return $this->hasMany(User::class,'prodi_id');
    }
}
