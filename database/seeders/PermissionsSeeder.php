<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Controllers\ChoreController;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    private $permissions = [
        [
            'name' => 'parent',
            'display_name' => 'Parent',
        ],
        [
            'name' => 'child',
            'display_name' => 'Child',
        ],

    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding Permissions');
        $this->command->getOutput()->progressStart(count($this->permissions));

        foreach ($this->permissions as $permission) {
            // $updateChore = $this->choreController->getChoreById($chore['has to be something else'])

            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
            ]);

            // $this->choreController->createChore(
            //     $chore['name'],
            //     $chore['description'],
            //     $chore['cost'],
            //     $chore['user_id'],
            //     $chore['approval_requested'],
            //     $chore['approval_request_date'],
            //     $chore['approval_status'],
            //     $chore['approval_date'],
            // );


            $this->command->getOutput()->progressAdvance();
        }
        $this->command->info(PHP_EOL);
    }
}
