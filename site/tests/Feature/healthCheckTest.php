<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class healthCheckTest extends TestCase
{
    /** @test */
    /** @test */
    public function health_check_response_ok()
    {
        $response = $this->json('GET', 'api/healthcheck',[], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJsonStructure([
                "message",
            ]);
    }
}
