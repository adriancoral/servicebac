<?php
namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait S3Manager
{
    /**
     * @param $filePath
     * @param $name
     * @return bool
     */
    protected function uploadFile($filePath, $name): bool
    {
        return Storage::disk('s3pdf')->put($this->getPath().$name, File::get($filePath));
    }

    /**
     * @param $fileUrl
     * @return bool
     */
    protected function deleteFile($fileUrl): bool
    {
        $filePath = Str::after($fileUrl, config('filesystems.s3_aws_pdf_cdn'));
        return Storage::disk('s3pdf')->delete($filePath);
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return Str::lower(config('filesystems.s3_aws_pdf_pdf_path'));
    }

    /**
     * @param $name
     * @return string
     */
    protected function getUri($name): string
    {
        return Str::lower(config('filesystems.s3_aws_pdf_cdn')).$this->getPath().$name;
    }

}
