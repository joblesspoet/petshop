<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Bouncer;

class RoleSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $now = Carbon::now();

        if (Bouncer::role()->first()) return;

        foreach (User::AVAILABLE_ROLES as $role) {

            $roleTitle = ucfirst($role);

            if (strpos($roleTitle, '_')) {
                // contains an underscore and is two words
                $roleTitle = $this->replaceUnderScoreBySpaces($roleTitle);
            }

            $record = [
                'title' => $roleTitle,
                'name' => $role,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            Bouncer::role()->updateOrCreate(['title' => $role], $record);
        }
    }

    /**
     * replaceUnderScoreBySpaces function
     *
     * @param string $role
     * @return string
     */
    public function replaceUnderScoreBySpaces(string $role): string
    {
        return ucwords(str_replace("_", " ", $role));
    }
}
