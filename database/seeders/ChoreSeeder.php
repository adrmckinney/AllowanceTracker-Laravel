<?php

namespace Database\Seeders;

use App\Data\Enums\ChoreApprovalStatuses;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Http\Controllers\ChoreController;
use Illuminate\Support\Facades\DB;

class ChoreSeeder extends Seeder
{
    private $chores = [
        [
            'name' => 'Mow front yard',
            'description' => 'Paid for mowing all of the front yard',
            'cost' => 500,
            'user_id' => 1,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => 0,
            'approval_date' => null,
        ],
        [
            'name' => 'Mow back yard',
            'description' => 'Paid for mowing all of the back yard',
            'cost' => 500,
            'user_id' => 1,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => 0,
            'approval_date' => null,
        ],
        [
            'name' => 'String Trim',
            'description' => 'Paid for string trimming front and back yard',
            'cost' => 500,
            'user_id' => 1,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => 0,
            'approval_date' => null,
        ],
        [
            'name' => 'Clean Kitchen',
            'description' => 'Paid for unloading and loading the dishwasher, cleaning stove top, cleaning counter tops, sweeping the floor',
            'cost' => 1000,
            'user_id' => 1,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => 0,
            'approval_date' => null,
        ],
        [
            'name' => 'Clean Bathrooms',
            'description' => 'Paid for cleaning toilets, sinks, floor, tub/shower of both bathrooms',
            'cost' => 1000,
            'user_id' => 1,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => 0,
            'approval_date' => null,
        ],
        [
            'name' => 'Grooming Roxie',
            'description' => 'Paid for bathing and brushing Roxie',
            'cost' => 500,
            'user_id' => 1,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => 0,
            'approval_date' => null,
        ],

    ];

    protected $choreController;

    public function __construct(ChoreController $choreController)
    {
        $this->choreController = $choreController;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding Chores');
        $this->command->getOutput()->progressStart(count($this->chores));

        foreach ($this->chores as $chore) {
            // $updateChore = $this->choreController->getChoreById($chore['has to be something else'])

            DB::table('chores')->insert([
                'name' => $chore['name'],
                'description' => $chore['description'],
                'cost' => $chore['cost'],
                'user_id' => $chore['user_id'],
                'approval_requested' => $chore['approval_requested'],
                'approval_request_date' => $chore['approval_request_date'],
                'approval_status' => $chore['approval_status'],
                'approval_date' => $chore['approval_date'],
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
