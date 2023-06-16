<?php

namespace Database\Seeders;

use App\Models\DataCategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(ApiuserSeeder::class);
        $this->call(DataSurveySeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(DataCategory::class);
    }
}
