<?php

namespace Database\Seeders;

use App\Models\ContactPerson;
use App\Models\Credential;
use App\Models\Link;
use App\Models\MailBox;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
        ]);
    }
}
