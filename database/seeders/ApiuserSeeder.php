<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class   ApiuserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('apiusers')->insert([
            [
                'client_access'=> 'a9425e84413431274b792d22271ea633',
                'access_secret'=> Hash::make('e4f52faf1f51354e98b2c2aee6'),
                'created_at'=>now(),
                'updated_at'=> now()
            ]
        ]);
    }
}
