<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Profile;


class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        foreach($users as $user) {
            if(!$user->profile) {
                $profile = new Profile();
                if($user->avatar)
                    $profile->avatar = $user->avatar;
                if($user->telegram_username)
                    $profile->telegram_username = $user->telegram_username;
                $profile->address = $user->address;
                $profile->save();
            }
        }
    }
}
