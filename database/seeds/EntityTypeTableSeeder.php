<?php

use Illuminate\Database\Seeder;
use App\EntityType;

class EntityTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\EntityType::truncate();  

        EntityType::insert([
            'entity_type' => 'p',
            'name' => 'Person',
        ]);

        EntityType::insert([
            'entity_type' => 'o',
            'name' => 'Organization',
        ]);
    }
}
