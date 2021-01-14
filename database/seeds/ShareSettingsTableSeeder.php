<?php

use Illuminate\Database\Seeder;
use App\ShareSetting;
use Carbon\Carbon;

class ShareSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ShareSetting::truncate(); 

        $setting = new ShareSetting;
        $setting->rate = 2;
        $setting->open_date = Carbon::today()->startOfMonth();
        $setting->close_date = Carbon::today()->endOfMonth();
        $setting->done_by = '17679';
        $setting->save();
    }
}
