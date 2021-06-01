<?php

namespace App\Console\Commands;

use App\Helper\Utils;
use App\Models\Epoch;
use App\Notifications\EpochEnd;
use App\Repositories\EpochRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckEndEpochNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:endepoch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $repo;

    public function __construct(EpochRepository $repo)
    {
        $this->repo = $repo;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $epoches = Epoch::with(['circle.protocol'])->whereNotNull('notified_before_end')->whereNull('notified_end')->get();

        foreach($epoches as $epoch) {
            $circle = $epoch->circle;

            if($circle->telegram_id && $epoch->number)
            {
                $protocol = $circle->protocol;
                $circle_name = $protocol->name.'/'.$circle->name;
                $circle->notify(new EpochEnd($epoch->number, $circle_name));
//                if($protocol->telegram_id) {
//                    $protocol->notify(new EpochEnd($epoch->number, $circle_name));
//                }
                $epoch->notified_end = Carbon::now();
                $epoch->save();
                Utils::purgeCache($circle->id);
            }
        }
    }
}
