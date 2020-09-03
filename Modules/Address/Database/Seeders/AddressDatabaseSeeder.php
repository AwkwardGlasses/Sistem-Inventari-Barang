<?php

namespace Modules\Address\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AddressDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $json_path = 'Modules/Address/Database/Resources/json';

        $provinces = db_resource_json('provinces', TRUE, $json_path);
        DB::table('provinces')->insert($provinces);

        $regencies = db_resource_json('regencies', TRUE, $json_path);
        DB::table('regencies')->insert($regencies);

        $districts = db_resource_json('districts', TRUE, $json_path);
        DB::table('districts')->insert($districts);
    }
}
