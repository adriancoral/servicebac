<?php

namespace Database\Factories;

use App\Models\FailedJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class FailedJobFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FailedJob::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid,
            'connection' => 'database',
            'queue' => 'default',
            'payload' => $this->generatePayload(),
            'exception' => 'Error: '.$this->faker->text(50),
        ];
    }

    private function generatePayload()
    {
        $job = $this->faker->lexify('App\\Jobs\\?????Job');
        return json_encode([
            'uuid' => $this->faker->uuid,
            'displayName' => $job,
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'maxTries' => null,
            'maxExceptions' => null,
            'failOnTimeout' => false,
            'backoff' => null,
            'timeout' => 360,
            'retryUntil' => null,
            'data' => [
                'commandName' => $job,
                'command' => "O:29:\"".$job."\":15:{s:7:\"timeout\";i:360;s:8:\"workCode\";s:10:\"6ctmtnvfvm\";s:7:\"fileUrl\";s:26:\"http:\/\/digitalsouth.com.ar\";s:10:\"payloadKey\";s:15:\"local-templates\";s:5:\"order\";i:0;s:3:\"job\";N;s:10:\"connection\";N;s:5:\"queue\";N;s:15:\"chainConnection\";N;s:10:\"chainQueue\";N;s:19:\"chainCatchCallbacks\";N;s:5:\"delay\";N;s:11:\"afterCommit\";N;s:10:\"middleware\";a:0:{}s:7:\"chained\";a:0:{}}"
            ],

        ]);

    }
}
