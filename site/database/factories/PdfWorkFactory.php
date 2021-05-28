<?php

namespace Database\Factories;

use App\Models\PdfWork;
use App\Traits\PdfWorkManager;
use App\Traits\S3Manager;
use Illuminate\Database\Eloquent\Factories\Factory;


class PdfWorkFactory extends Factory
{
    use PdfWorkManager;
    use S3Manager;
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PdfWork::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $workCode = $this->getWorkCode();
        $fileName = $this->getFileName($workCode);
        return [
            'code' => $workCode,
            'payload' => json_encode($this->payload()),
            'file_name' => $fileName,
            'status' => $this->faker->randomElement(['in_progress','fail','done']),
            'message' => '',
            'link' => $this->getUri($fileName),
            'callback' => ($this->faker->boolean()) ? $this->faker->url() : null
        ];
    }

    /**
     * @return array
     */
    private function payload(): array
    {
        $payload['content'] = [
                'title' => $this->faker->title(),
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
                'address' => $this->faker->address(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'country' => $this->faker->country(),
            ];
        $payload['templates'] = [];

        if ($this->faker->boolean()){
            $payload['attachments'] = [$this->faker->url()];
        }

        if ($this->faker->boolean()){
            $payload['callback'] = [$this->faker->url()];
        }

        return $payload;
    }
}
