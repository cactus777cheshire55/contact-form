<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    protected function faker()
    {
        return \Faker\Factory::create('ja_JP');
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = $this->faker();

        return [
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
            'first_name' => $faker->lastName(),
            'last_name' => $faker->firstName(),
            'gender' => $faker->randomElement([1, 2, 3]),
            'email' => $faker->unique()->safeEmail(),
            'tel' => $faker->numerify('0##########'),
            'address' => fake()->prefecture().fake()->city().fake()->streetAddress(),
            'building' => $faker->secondaryAddress(),
            'detail' => $faker->realText(120),
        ];
    }
}
