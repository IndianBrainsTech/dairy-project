<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * The Role model class.
 *
 * This class represents role definitions in the application and is based on
 * Spatie's permission package. It includes support for internal role names,
 * display-friendly labels, and status field.
 *
 * Inherits:
 * - Spatie\Permission\Models\Role: Provides core role management functionality,
 *   including relationships to users and permissions.
 */
class Role extends SpatieRole
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'guard_name', 'display_name', 'status',
    ];
}