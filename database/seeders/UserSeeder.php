<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Controllers\UserController;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    private $users = [
        [
            'name' => 'Daniel McKinney',
            'email' => 'adrmckinney@gmail.com',
            'username' => 'adrmckinney',
            'wallet' => 0,
            'password' => 'password',
        ],
    ];

    protected $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding Users');
        $this->command->getOutput()->progressStart(count($this->users));

        foreach ($this->users as $user) {
            // $updateChore = $this->choreController->getChoreById($chore['has to be something else'])
            if (!$this->userController->usernameExists($user['username']) || !$this->userController->userEmailExists($user['email'])) {
                User::factory()->create([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'wallet' => $user['wallet'],
                    'password' => Hash::make($user['password']),
                ]);
            }


            $this->command->getOutput()->progressAdvance();
        }
        $this->command->info(PHP_EOL);
    }
}
