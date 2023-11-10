<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $genders = ['male', 'female'];
        $gender = $genders[array_rand($genders)];

        $statuses = ['Lead', 'Opportunity', 'Customer', 'Close'];
        $status = $statuses[array_rand($statuses)];

        return [
            'first_name' => fake()->firstName($gender),
            'middle_name' => fake()->lastName(),
            'last_name' => fake()->lastName(),
            'prefix' => fake()->title($gender),
            'status' => $status,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->tollFreePhoneNumber(),
            'mobile' => fake()->e164PhoneNumber(),
            'fax' => fake()->phoneNumber(),
            'referral_source' => null,
            'company' => fake()->company(),
            'position_title' => fake()->jobTitle(),
            'industry' => fake()->bs(),
            'website' => fake()->url(),
            'linkedin' => null,
            'address_street' => fake()->streetAddress(),
            'address_city' => fake()->city(),
            'address_state' => fake()->state(),
            'address_country' => fake()->country(),
            'address_zipcode' => fake()->postcode(),
            'created_by' => User::factory(),
            'modified_by' => null,
            'assigned_to' => null,
        ];
    }
}
