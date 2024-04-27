<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\ProductImage;
use App\Models\Products;
use App\Models\SpecialOffers;
use App\Models\User as UserAlias;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (UserAlias::count() == 0) UserAlias::factory(10)->create();

        if (Categories::count() == 0) {
            Categories::factory(20)->create();
        }

        if (Products::count() == 0) {
            for ($y = 0; $y < 50; $y++) {
                Products::factory(1)->create();
                $numberOfIterations = rand(1, 5);
                for ($i = 0; $i < $numberOfIterations; $i++) {
                    ProductImage::factory(1)->create();
                }
            }
        }

        if (SpecialOffers::count() == 0) SpecialOffers::factory(5)->create();
    }
}
