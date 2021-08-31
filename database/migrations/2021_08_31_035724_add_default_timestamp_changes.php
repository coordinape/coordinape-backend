<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultTimestampChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE users
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE vouches
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE token_gifts
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE teammates
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE protocols
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE profiles
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE pending_token_gifts
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE nominees
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE histories
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE feedbacks
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE epoches
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        DB::statement('ALTER TABLE circles
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE users
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE vouches
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE token_gifts
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE teammates
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE protocols
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE profiles
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE pending_token_gifts
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE nominees
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE histories
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE feedbacks
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE epoches
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');

        DB::statement('ALTER TABLE circles
            MODIFY COLUMN created_at TIMESTAMP NULL DEFAULT NULL,
            MODIFY COLUMN updated_at TIMESTAMP NULL DEFAULT NULL');
    }
}
