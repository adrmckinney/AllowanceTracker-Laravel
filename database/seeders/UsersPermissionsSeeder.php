<?php

namespace Database\Seeders;

use App\Data\Enums\PermissionTypes;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Models\Permission;
use App\Models\User;
use App\Models\UsersPermissions;
use DateTime;
use Illuminate\Database\Seeder;

class UsersPermissionsSeeder extends Seeder
{
    protected $userController, $permissionsController, $usersPermissionsController;

    public function __construct(
        UserController $userController,
        PermissionsController $permissionsController,
        UserPermissionController $usersPermissionsController
    ) {
        $this->userController = $userController;
        $this->permissionsController = $permissionsController;
        $this->usersPermissionsController = $usersPermissionsController;
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $startTime = new DateTime();
        $this->command->info('Seeding UserPermissions');

        $users = User::all();
        $permissions = Permission::all();

        $this->command->getOutput()->progressStart(count($users) * count($permissions));

        foreach ($users as $user) {
            if (!$this->usersPermissionsController->userPermissionExists($user->id)) {
                switch ($user->username) {
                    case 'adrmckinney':
                        UsersPermissions::create([
                            'user_id' => $user->id,
                            'permission_id' => PermissionTypes::$ADMIN
                        ]);
                        break;
                    case 'cd_mck':
                        UsersPermissions::create([
                            'user_id' => $user->id,
                            'permission_id' => PermissionTypes::$PARENT
                        ]);
                        break;
                    case 'abbey_mck':
                    case 'olivia_mck':
                    case 'matthew_mck':
                        UsersPermissions::create([
                            'user_id' => $user->id,
                            'permission_id' => PermissionTypes::$CHILD
                        ]);
                        break;
                    default:
                        break;
                }
            }
            $this->command->getOutput()->progressAdvance();
        }
        $this->command->info(PHP_EOL);
        $executionTime = $startTime->diff(new DateTime());
        $elapse = $executionTime->format('%i minutes %s.%F seconds');
        $this->command->info('Command execution time: ' . $elapse . PHP_EOL);
    }
}
