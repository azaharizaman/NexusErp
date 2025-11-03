<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Actions\User\AssignPermissionToUser;
use App\Actions\User\RevokePermissionFromUser;

class ManageUserPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:permission 
                            {action : assign or revoke}
                            {email : User email}
                            {permission : Permission name}';

    /**
     * The console command description.
     */
    protected $description = 'Assign or revoke permissions for a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $email = $this->argument('email');
        $permission = $this->argument('permission');

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        try {
            if ($action === 'assign') {
                AssignPermissionToUser::run($user, $permission);
                $this->info("Permission '{$permission}' assigned to user '{$user->name}' ({$user->email}) successfully.");
            } elseif ($action === 'revoke') {
                RevokePermissionFromUser::run($user, $permission);
                $this->info("Permission '{$permission}' revoked from user '{$user->name}' ({$user->email}) successfully.");
            } else {
                $this->error("Invalid action. Use 'assign' or 'revoke'.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Failed to {$action} permission: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}