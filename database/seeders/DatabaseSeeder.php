<?php

namespace Database\Seeders;

use App\Services\AdminAccountManager;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function __construct(private readonly AdminAccountManager $adminAccountManager)
    {
    }

    public function run(): void
    {
        $this->adminAccountManager->sync();
        $this->call(FoodSeeder::class);
    }
}