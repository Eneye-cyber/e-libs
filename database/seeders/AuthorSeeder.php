<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AuthorSeeder extends Seeder   
{

    // use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Author::factory(25)->create();

    }
}
