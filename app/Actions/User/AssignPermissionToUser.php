<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Permission;

class AssignPermissionToUser
{
    use AsAction;

    /**
     * Assign a permission to a user
     */
    public function handle(User $user, string $permissionName): User
    {
        // Create permission if it doesn't exist
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);

        // Assign permission to user if they don't already have it
        if (! $user->hasPermissionTo($permissionName)) {
            $user->givePermissionTo($permission);
        }

        return $user->fresh();
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'permissionName' => 'required|string|max:255',
        ];
    }

    /**
     * Action authorization
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('manage_permissions');
    }
}
