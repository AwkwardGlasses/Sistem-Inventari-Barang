<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ACLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = db_resource_json('permissions', TRUE);
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        $roles = db_resource_json('roles', TRUE);
        foreach ($roles as $role) {
            $roleData = $role;
            unset($roleData['permissions']);

            $permissions = $role['permissions'];
            if (in_array("all", $permissions)) {
                $permissions = Permission::all();
                Log::debug('permissions all', [ $permissions ]);
            } else {
                $permissions = Permission::whereIn('name', $permissions)->get();
                Log::debug('permissions in', [ $permissions ]);
            }

            $roleObj = Role::create($roleData);
            $roleObj->syncPermissions($permissions);
        }

        $users = db_resource_json('users', TRUE);
        foreach ($users as $user) {
            $userData = $user;
            $userData['password'] = Hash::make($user['password']);
            $userData['email'] = config('app.owner_email');
            unset($userData['roles']);
            $userObj = User::create($userData);
            $userObj->syncRoles($user['roles']);
        }
    }
}
