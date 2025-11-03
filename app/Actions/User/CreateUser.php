<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction;

    /**
     * Create a new user
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => ($data['email_verified'] ?? false) ? now() : null,
            ]);

            // Send welcome email if specified
            if ($data['send_welcome_email'] ?? false) {
                // You can implement welcome email sending here
                // Mail::to($user)->send(new WelcomeEmail());
            }

            return $user;
        });
    }

    /**
     * Validation rules for user creation
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'email_verified' => ['boolean'],
            'send_welcome_email' => ['boolean'],
        ];
    }

    /**
     * Action authorization
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('create_users');
    }
}
