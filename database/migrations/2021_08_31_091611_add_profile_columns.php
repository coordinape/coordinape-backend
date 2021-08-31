<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class AddProfileColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('ann_power')->default(0);
            $table->string('chat_id')->nullable();
        });
        $users = User::with('profile')->get();
        foreach($users as $user) {
            $profile = $user->profile;
            if(!$profile->chat_id && $user->chat_id ) {
                $profile->chat_id = $user->chat_id;
                $profile->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('ann_power','chat_id');
        });
    }
}
