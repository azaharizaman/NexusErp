<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword
{
    use AsAction;

    /**
     * Update user password
     */
    public function handle(User $user, string $newPassword): User
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return $user->refresh();
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }

    /**
     * Action authorization
     */
    public function authorize(User $user): bool
    {
        return auth()->check() && (
            auth()->user()->can('update_users') ||
            auth()->id() === $user->id
        );
    }
}