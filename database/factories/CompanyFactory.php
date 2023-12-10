<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['Lead', 'Opportunity', 'Customer', 'Close'];
        $status = $statuses[array_rand($statuses)];

        $callStatuses = [
            "Unprocessed",
            "Not reached",
            "Already a customer",
            "Wrong number",
            "Has no interest",
            "Website under construction",
            "Company non-existent",
            "Duplicate entry",
            "Doesn't want to be called anymore",
            "Call again on Date",
            "Set Appointment Date"
        ];
        // $callStatuses = ["Unprocessed", "Not Reached", "Not Interested", "Doesn't want to be called anymore", "Call again on Date", "Set Appointment Date"];
        $callStatus = $callStatuses[array_rand($callStatuses)];

        return [
            'team_id' => 1,
            'name' => fake()->company(),
            'description' => fake()->catchPhrase(),
            'industry' => fake()->bs(),
            'total_employees' => fake()->numberBetween(5, 500),
            'email' => fake()->unique()->safeEmail(),
            'website' => 'www.' . fake()->domainName(),
            'linkedin' => null,
            'referral_source' => null,
            'address_street' => fake()->streetAddress(),
            'address_city' => null,
            'address_state' => null,
            'address_country' => fake()->country(),
            'address_zipcode' => null,
            'status' => $status,
            'call_status' => $callStatus,
            'follow_up_date' => null,
            'appointment_date' => null,
            'created_by' => 1,
            'modified_by' => null,
            'assigned_caller' => null,
            'assigned_consultant' => null,
        ];
    }
}
