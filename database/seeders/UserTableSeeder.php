<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $userFactory =  User::factory();

        /**
         * Admin
         */
        $admin = clone $userFactory->create(
            [
                'first_name' => 'App',
                'last_name' => 'Administrator',
                'email' => 'admin@buckhill.co.uk',
                'password' => Hash::make('admin'), // admin
                'email_verified_at' => now()
            ]
        );
        // Assign roles
        $admin->assign(User::ROLE_ADMIN);


        $userOne = clone $userFactory->create();
        $userOne->assign(User::ROLE_USER);

        $userTwo = clone $userFactory->create();
        $userTwo->assign(User::ROLE_USER);

        $userThree = clone $userFactory->create();
        $userThree->assign(User::ROLE_USER);

    }
}
