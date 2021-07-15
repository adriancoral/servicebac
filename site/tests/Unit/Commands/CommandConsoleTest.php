<?php

namespace Tests\Unit\Commands;

use App\Models\FailedJob;
use App\Models\PdfWork;
use App\Notifications\MessageToSlack;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

class CommandConsoleTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function clean_pdf_folder_delete_old_folder()
    {
        PdfWork::flushEventListeners();

        Storage::fake('localdisk');

        $workDone = PdfWork::factory()->create(['status' => 'done']);
        $destDone = config('filesystems.local_pdf_path').'/'.$workDone->code.'/'.Str::lower(Str::random(5)).'.blade.php';
        Storage::disk('localdisk')->put($destDone, 'test_content');

        Storage::disk('localdisk')->assertExists($destDone);

        $workFail = PdfWork::factory()->create(['status' => 'fail']);
        $destFail = config('filesystems.local_pdf_path').'/'.$workFail->code.'/'.Str::lower(Str::random(5)).'.blade.php';
        Storage::disk('localdisk')->put($destFail, 'test_content');

        Storage::disk('localdisk')->assertExists($destFail);

        $this->artisan('pdf-service:clean-folder')
            ->assertExitCode(0);

        Storage::disk('localdisk')->assertMissing($destFail);
        Storage::disk('localdisk')->assertMissing($destDone);
    }

    /** @test */
    public function clean_pdf_folder_dont_get_works_with_status_in_progress()
    {
        PdfWork::flushEventListeners();

        Storage::fake('localdisk');

        $workInProgress = PdfWork::factory()->create(['status' => 'in_progress']);
        $destInProgress = config('filesystems.local_pdf_path').'/'.$workInProgress->code.'/'.Str::lower(Str::random(5)).'.blade.php';
        Storage::disk('localdisk')->put($destInProgress, 'test_content');

        Storage::disk('localdisk')->assertExists($destInProgress);

        $this->artisan('pdf-service:clean-folder')
            ->assertExitCode(0);

        Storage::disk('localdisk')->assertExists($destInProgress);
    }

    /** @test */
    public function delete_old_pdf_works_get_only_works_in_status_fail_or_done_only_to_delete_after_24_hs()
    {
        PdfWork::flushEventListeners();

        $pastDate = Carbon::now()->subHours(25);

        PdfWork::factory()->create(['status' => 'in_progress']);
        PdfWork::factory(3)->create(['status' => 'done', 'created_at' => $pastDate]);
        PdfWork::factory()->create(['status' => 'fail', 'created_at' => $pastDate]);

        $this->assertCount(5, PdfWork::All());

        $this->artisan('pdf-service:delete-old-pdfworks')
            ->assertExitCode(0);

        $this->assertCount(1, PdfWork::All());
    }

    /** @test */
    public function clean_failed_jobs_delete_failed_jobs_table()
    {
        FailedJob::factory(5)->create();

        $this->assertCount(5, FailedJob::All());

        $this->artisan('pdf-service:clean-failed-jobs_db')
            ->assertExitCode(0);

        $this->assertCount(0, FailedJob::All());
    }

    /** @test */
    public function cancel_exceeded_time_pdfworks_change_status_to_fail_on_exceeded_time_pdfworks()
    {
        PdfWork::flushEventListeners();

        $pastDate = Carbon::now()->subMinutes(16);

        PdfWork::factory()->create(['status' => 'in_progress', 'updated_at' => $pastDate]);
        PdfWork::factory(2)->create(['status' => 'in_progress']);
        PdfWork::factory()->create(['status' => 'done']);
        PdfWork::factory()->create(['status' => 'fail']);

        $this->assertCount(5, PdfWork::All());
        $this->assertCount(1, PdfWork::where('status', 'fail')->get());
        $this->assertCount(3, PdfWork::where('status', 'in_progress')->get());

        $this->artisan('pdf-service:cancel-exceeded-time-pdfworks')
            ->assertExitCode(0);

        $this->assertCount(5, PdfWork::All());
        $this->assertCount(2, PdfWork::where('status', 'fail')->get());
        $this->assertCount(2, PdfWork::where('status', 'in_progress')->get());
    }

    /** @test */
    public function monitoring_undelivered_pdfworks_send_notification_if_found_three_callback_tries()
    {
        PdfWork::flushEventListeners();
        Queue::fake();

        $data = [
            'tries' => [
                Carbon::now(),
                Carbon::now(),
                Carbon::now(),
            ],
            'response' => [
                'message' => 'Hello World',
                'success' => false
            ]
        ];

        PdfWork::factory()->create(['callback_response' => $data]);
        PdfWork::factory(2)->create();

        $this->assertCount(3, PdfWork::All());

        $this->artisan('pdf-service:monitoring-undelivered-pdfworks')
            ->expectsOutput('Monitoring End')
            ->assertExitCode(0);

        Queue::assertNotPushed(MessageToSlack::class);

        $this->assertCount(1, PdfWork::whereNotNull('internal_status')->get());
    }
}
