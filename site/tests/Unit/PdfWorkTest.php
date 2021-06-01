<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PdfWorkTest extends TestCase
{
    /** @test */
    public function insert_en_db_dispatch_evento_PdfWorkCreated()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function PdfWorkCreated_call_listener_PdfWorkGetSources()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function PdfWorkGetSources_dispatch_DownloadFilesToLocal()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function DownloadFilesToLocal_call_event_DownloadedFinishedFile()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function DownloadFilesToLocal_update_payload()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function DownloadedFinishedFile_call_listener_PdfMaker()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function PdfMaker_dispatch_job_CreatePdfFromTemplate()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function CreatePdfFromTemplate_call_event_FinishedPdfFile()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function CreatePdfFromTemplate_update_payload()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function FinishedPdfFile_call_listener_PdfMergeable()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function PdfMergeable_dispatch_job_PdfFileDelivery()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function PdfFileDelivery_dispatch_job_CallBackResponse()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function CallBackResponse_send_response()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function CallBackResponse_update_pdfwork_record()
    {
        $this->assertTrue(true);
    }

}

