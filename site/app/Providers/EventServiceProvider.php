<?php

namespace App\Providers;

use App\Events\DownloadedFinishedFile;
use App\Events\FinishedPdfFile;
use App\Events\PdfWorkCreated;
use App\Listeners\PdfMergeable;
use App\Listeners\PdfMaker;
use App\Listeners\PdfWorkGetSources;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PdfWorkCreated::class => [
            PdfWorkGetSources::class,
        ],
        DownloadedFinishedFile::class => [
            PdfMaker::class
        ],
        FinishedPdfFile::class => [
            PdfMergeable::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
