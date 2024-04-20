<?php

namespace Database\Factories;

use App\Models\Categories;
use App\Models\ProductImage;
use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\Factory;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productId = Products::max('id');

        $imageUrl = "https://source.unsplash.com/random/?product";
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

        $priority = ProductImage::where('product_id', $productId)->max('priority') + 1 ?? 1;

        return [
            'name' => $imageName,
            'product_id' => $productId,
            'priority' => $priority
        ];
    }
}
