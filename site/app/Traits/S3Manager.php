<?php
namespace App\Traits;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait S3Manager
{
    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function uploadFile(UploadedFile $file)
    {
        return Str::lower(config('filesystems.s3_aws_cdn')).$file->storeAs(
            $this->getPath(),
            $this->getName(),
            's3'
        );
    }

    /**
     * @param $fileUrl
     * @return bool
     */
    protected function deleteFile($fileUrl): bool
    {
        $filePath = Str::after($fileUrl, config('filesystems.s3_aws_cdn'));
        return Storage::disk('s3')->delete($filePath);
    }

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return Str::lower(config('filesystems.s3_aws_pdf_path'));
    }

    protected function getUri($name): string
    {
        return Str::lower(config('filesystems.s3_aws_cdn')).$this->getPath().$name;
    }

}
