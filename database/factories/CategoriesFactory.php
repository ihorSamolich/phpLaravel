<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categories>
 */
class CategoriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imageUrl = "https://source.unsplash.com/random/?food";
        $imageContent = file_get_contents($imageUrl);

        $folderName = public_path('uploads');
        if (!file_exists($folderName)) {
            mkdir($folderName, 0777);
        }

        $imageName = uniqid() . ".webp";
        $sizes = [100, 300, 500];
        $manager = new ImageManager(new Driver());
        foreach ($sizes as $size) {
            $fileSave = $size . "_" . $imageName;
            $imageRead = $manager->read($imageContent);
            $imageRead->scale(width: $size);
            $path = public_path('uploads/' . $fileSave);
            $imageRead->toWebp()->save($path);
        }

        return [
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
            'image' => $imageName,
        ];
    }
}