<?php

namespace App\Console\Commands;

use App\Models\PdfWork;
use App\Notifications\MessageToSlack;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MonitoringUndeliveredPdfWorks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf-service:monitoring-undelivered-pdfworks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search PdfWorks with 3 failed tries to callback and send slack notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $pdfWorks = PdfWork::whereJsonLength('callback_response->tries', 3)->get();

        $filtered = $pdfWorks->filter(function ($row, $key) {
            if (!isset($row->internal_status['undelivered'])) {
                return $row;
            }
        });

        $filtered->each(function ($work, $key) {
            $work->update(['internal_status->undelivered' => ['notification' => Carbon::now()]]);
            Notification::route('slack', config('failed-job-monitor.slack.webhook_url'))
                ->notify(new MessageToSlack('Undelivered PdfWork: '.$work->code, $this->getAttachments($work)));
        });
        Log::info('Notification send: '.$filtered->count());
        $this->info('Monitoring End');
        return 0;
    }

    /**
     * @param $work
     * @return array
     */
    private function getAttachments($work): array
    {
        return [
            'Link Status' => route('pdf.status', $work->code),
            'CallBackResponse' => json_encode($work->callback_response),
        ];
    }
}
