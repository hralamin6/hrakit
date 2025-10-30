<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      User::updateOrCreate([
        'name' => 'admin',
        'email' => 'admin@mail.com'], [
        'email_verified_at' => now(),
        'password' => bcrypt('000000'),
      ]);
      User::updateOrCreate([
        'name' => 'user',
        'email' => 'user@mail.com'], [
        'email_verified_at' => now(),
        'password' => bcrypt('000000'),
      ]);
    }
}
