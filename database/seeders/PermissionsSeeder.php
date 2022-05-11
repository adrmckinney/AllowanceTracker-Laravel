<?php

namespace Database\Seeders;

use App\Http\Controllers\PermissionsController;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use DateTime;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    private $permissions = [
        [
            'name' => 'no_access',
            'display_name' => 'No Access',
        ],
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

    protected $permissionsController;

    public function __construct(PermissionsController $permissionsController)
    {
        $this->permissionsController = $permissionsController;
    }

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
            if (!$this->permissionsController->permissionExists($permission['name'])) {
                Permission::factory()->create([
                    'name' => $permission['name'],
                    'display_name' => $permission['display_name'],
                ]);
            }

            $this->command->getOutput()->progressAdvance();
        }
        $this->command->info(PHP_EOL);
    }
}
