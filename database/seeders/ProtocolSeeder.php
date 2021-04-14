<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Protocol;
use App\Models\Circle;
use App\Models\Epoch;

class ProtocolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $yearn = new Protocol(['name'=>'yearn']);
        $yearn->save();
        Circle::where('id',2)->orWhere('id',3)->update(['protocol_id'=>$yearn->id]);
        Circle::where('id',1)->update(['protocol_id'=>$yearn->id, 'name' => 'community']);

        $sushi = new Protocol(['name'=>'sushi']);
        $sushi->save();
        Circle::where('id',4)->update(['protocol_id'=>$sushi->id,'name'=>'community']);

        $cream = new Protocol(['name'=>'cream']);
        $cream->save();

        Epoch::where('id','>=',5)->update(['ended'=>0]);
    }
}
