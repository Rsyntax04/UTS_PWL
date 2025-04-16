<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use softDeletes; // Enable soft deletes
    use HasFactory, Notifiable, HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $primaryKey = 'nrp'; // Set NRP as primary key
    public $incrementing = false; // Disable auto-increment
    protected $keyType = 'int'; // Define key type as string
    protected $fillable = [
        'name',
        'nrp', // Added nrp (student/employee number)
        'prodi_id', // Added prodi_id (study program)
        'email',
        'password',
        'isDosen',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Auto-generate email from NRP if not set
            if (empty($user->email) && !empty($user->nrp)) {
                $user->email = $user->nrp . '@maranatha.ac.id';
            }

            // Auto-generate and hash password if not set
            if (empty($user->password)) {
                $user->password = Hash::make($user->nrp);
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id', 'prodi_id');
    }
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'nrp'); // Adjust based on your actual foreign key
    }
}
