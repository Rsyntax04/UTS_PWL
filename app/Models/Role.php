<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $fillable = [
        'name', // Spatie expects 'name' instead of 'role_name'
        'guard_name', // Required by Spatie
    ];

    
}
