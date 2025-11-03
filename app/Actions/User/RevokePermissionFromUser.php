<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class RevokePermissionFromUser
{
    use AsAction;

    /**
     * Revoke a permission from a user
     */
    public function handle(User $user, string $permissionName): User
    {
        if ($user->hasPermissionTo($permissionName)) {
            $user->revokePermissionTo($permissionName);
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