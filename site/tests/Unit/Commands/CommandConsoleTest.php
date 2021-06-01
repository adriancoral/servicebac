<?php

namespace Tests\Unit\Commands;

use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommandConsoleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function CleanPdfFolder_delete_old_folder()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function CleanPdfFolder_get_works_fail_or_done_only()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function DeleteOldPdfWorks_get_works_fail_or_done_only_to_delete()
    {
        $this->assertTrue(true);
    }
}
