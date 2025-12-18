<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use App\Enums\ReportType;
use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = $this->getRandomUserId();
        $reportedUserId = $this->getRandomReportedUserId($userId);

        return [
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(ReportType::values()),
            'status' => $this->faker->randomElement(ReportStatus::values()),
            'user_id' => $userId,
            'reported_user_id' => $reportedUserId,
        ];
    }

    /**
     * Get a random existing user ID with the 'user' role.
     */
    private function getRandomUserId(): ?int
    {
        return User::role('user')->inRandomOrder()->value('id');
    }

    /**
     * Get a random reported user ID that is different from the given user ID.
     */
    private function getRandomReportedUserId(int $userId): ?int
    {
        return User::role('user')->where('id', '!=', $userId)->inRandomOrder()->value('id');
    }

    /**
     * Indicate that the report is in 'waiting' status.
     */
    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::WAITING,
        ]);
    }

    /**
     * Indicate that the report is 'accepted'.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::ACCEPTED,
        ]);
    }

    /**
     * Indicate that the report is 'rejected'.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::REJECTED,
        ]);
    }

    /**
     * Indicate a specific report type.
     */
    public function ofType(ReportType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type->value,
        ]);
    }
}
