<?php

namespace Database\Seeders;

use App\Data\Enums\PermissionTypes;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Models\Permission;
use App\Models\UsersPermissions;
use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

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

        $users = $this->userController->getUsers();
        $permissions = Permission::all();

        $this->command->getOutput()->progressStart(count($users) * count($permissions));

        foreach ($users as $user) {
            if ($this->userController->getUserByUsername($user->username)->first()->username === "adrmckinney") {
                UsersPermissions::factory()->create([
                    'user_id' => $user->id,
                    'permission_id' => PermissionTypes::$ADMIN
                ]);

                $this->command->getOutput()->progressAdvance();
            }
        }
        $this->command->info(PHP_EOL);
        $executionTime = $startTime->diff(new DateTime());
        $elapse = $executionTime->format('%i minutes %s.%F seconds');
        $this->command->info('Command execution time: ' . $elapse . PHP_EOL);
    }
}
