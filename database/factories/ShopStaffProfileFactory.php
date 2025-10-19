<?php

namespace Database\Factories;

use App\Models\ShopStaffProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShopStaffProfile>
 */
class ShopStaffProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ShopStaffProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_staff_id' => null, // Seeder 側で設定
            'nickname' => $this->faker->name(),
            'small_image_url' => null,
            'large_image_url' => null,
        ];
    }
}
