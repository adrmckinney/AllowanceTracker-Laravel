<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Controllers\ChoreController;
use App\Models\Chore;

class ChoreSeeder extends Seeder
{
    private $chores = [
        [
            'name' => 'Mow front yard',
            'description' => 'Paid for mowing all of the front yard',
            'cost' => 500,
        ],
        [
            'name' => 'Mow back yard',
            'description' => 'Paid for mowing all of the back yard',
            'cost' => 500,
        ],
        [
            'name' => 'String Trim',
            'description' => 'Paid for string trimming front and back yard',
            'cost' => 500,
        ],
        [
            'name' => 'Clean Kitchen',
            'description' => 'Paid for unloading and loading the dishwasher, cleaning stove top, cleaning counter tops, sweeping the floor',
            'cost' => 1000,
        ],
        [
            'name' => 'Clean Bathrooms',
            'description' => 'Paid for cleaning toilets, sinks, floor, tub/shower of both bathrooms',
            'cost' => 1000,
        ],
        [
            'name' => 'Grooming Roxie',
            'description' => 'Paid for bathing and brushing Roxie',
            'cost' => 500,
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
            // $updateChore = $this->choreController->getChoreById($chore['has to be something else']);

            if (!$this->choreController->choreExists($chore['name'])) {
                Chore::factory()->create([
                    'name' => $chore['name'],
                    'description' => $chore['description'],
                    'cost' => $chore['cost'],
                ]);
            }

            $this->command->getOutput()->progressAdvance();
        }
        $this->command->info(PHP_EOL);
    }
}
