<?php

namespace Database\Seeders;

use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\UserController;
use App\Models\Permission;
use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class UsersPermissionsSeeder extends Seeder
{
    protected $userController, $permissionsController;

    public function __construct(UserController $userController, PermissionsController $permissionsController)
    {
        $this->userController = $userController;
        $this->permissionsController = $permissionsController;
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
                $userPermission = new Request([
                    'user_id' => $user->id,
                    'name' => 'admin'
                ]);

                $this->permissionsController->addPermission($userPermission);

                $this->command->getOutput()->progressAdvance();
            }
        }
        $this->command->info(PHP_EOL);
        $executionTime = $startTime->diff(new DateTime());
        $elapse = $executionTime->format('%i minutes %s.%F seconds');
        $this->command->info('Command execution time: ' . $elapse . PHP_EOL);
    }
}
