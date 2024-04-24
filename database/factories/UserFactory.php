<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $imageUrl = "https://source.unsplash.com/random/?person";
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'image' => $imageName,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
