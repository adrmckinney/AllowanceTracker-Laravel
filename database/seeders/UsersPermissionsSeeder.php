<?php

namespace Database\Seeders;

use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\UserController;
use App\Models\Permission;
use App\Models\User;
use App\Types\Users\UserPermissionType;
use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class UsersPermissionsSeeder extends Seeder
{
    // protected $tiersFeaturesRepo;

    // public function __construct(TierFeatureRepo $tiersFeaturesRepo)
    // {
    //     $this->tiersFeaturesRepo = $tiersFeaturesRepo;
    // }

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
            if (UserController::getUserByName($user->name)->first()->name === "Daniel McKinney") {
                $userPermission = new Request([
                    'user_id' => $user->id,
                    'name' => 'parent'
                ]);

                PermissionsController::addPermission($userPermission);

                $this->command->getOutput()->progressAdvance();
            }
        }
        $this->command->info(PHP_EOL);
        $executionTime = $startTime->diff(new DateTime());
        $elapse = $executionTime->format('%i minutes %s.%F seconds');
        $this->command->info('Command execution time: ' . $elapse . PHP_EOL);
    }
}
