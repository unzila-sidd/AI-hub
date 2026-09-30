<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function assignPermission(Permission|string $permission): void
    {
        $permission = is_string($permission)
            ? Permission::firstOrCreate(['slug' => $permission], ['name' => ucfirst(str_replace('_', ' ', $permission)), 'group' => 'other'])
            : $permission;

        $this->permissions()->syncWithoutDetaching($permission->id);
    }
}