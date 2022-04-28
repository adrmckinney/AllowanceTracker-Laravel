<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Controllers\ChoreController;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    private $permissions = [
        [
            'name' => 'admin',
            'display_name' => 'Admin',
        ],
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

            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
            ]);

            $this->command->getOutput()->progressAdvance();
        }
        $this->command->info(PHP_EOL);
    }
}
