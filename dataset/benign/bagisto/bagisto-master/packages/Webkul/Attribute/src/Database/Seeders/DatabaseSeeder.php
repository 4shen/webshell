<?php

namespace Webkul\Attribute\Database\Seeders;

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
        $this->call(AttributeFamilyTableSeeder::class);
        $this->call(AttributeGroupTableSeeder::class);
        $this->call(AttributeTableSeeder::class);
        $this->call(AttributeOptionTableSeeder::class);
    }
}
