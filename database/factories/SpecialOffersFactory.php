<?php

namespace Database\Factories;

use App\Models\Categories;
use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\Factory;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecialOffers>
 */
class SpecialOffersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Products::inRandomOrder()->first();

        $imageUrl = "https://picsum.photos/1800/800";
        $imageContent = file_get_contents($imageUrl);

        $folderName = public_path('uploads');
        if (!file_exists($folderName)) {
            mkdir($folderName, 0777);
        }

        $imageName = uniqid() . ".webp";
        $sizes = [1200, 1800];
        $manager = new ImageManager(new Driver());
        foreach ($sizes as $size) {
            $fileSave = $size . "_" . $imageName;
            $imageRead = $manager->read($imageContent);
            $imageRead->scale(width: $size);
            $path = public_path('uploads/' . $fileSave);
            $imageRead->toWebp()->save($path);
        }

        return [
            'description' => $this->faker->sentence,
            'product_id' => $product->id,
            'image' => $imageName,
        ];
    }
}
