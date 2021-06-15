<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateRegift extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $users = User::with('pendingReceivedGifts')->where('regift_percent',100)->get();
        foreach($users as $user) {
            $pendingGifts = $user->pendingReceivedGifts;
            if(count($pendingGifts)) {
                $pendingGifts->load(['sender.pendingSentGifts']);
                foreach($pendingGifts as $gift) {
                    if(!$gift->tokens && $gift->note)
                        continue;

                    $sender = $gift->sender;
                    $gift_token = $gift->tokens;
                    $gift->delete();
                    $token_used = $sender->pendingSentGifts->SUM('tokens') - $gift_token;
                    $sender->give_token_remaining = $sender->starting_tokens - $token_used;
                    $sender->save();
                }
                $updateData['give_token_received'] = 0;
            }
            $updateData['non_receiver'] = 1;
            $user->update($updateData);
        }
    }
}
