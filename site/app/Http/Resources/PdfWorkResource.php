<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PdfWorkResource extends JsonResource
{
    public $with = ['success' => true];

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $body = [];
        foreach (self::responseData() as $field) {
            $body[$field] = $this->$field;
        }

        $body['started_process'] = $this->created_at;
        $body['finished_process'] = $this->updated_at;

        return $body;
    }

    public static function responseData()
    {
        return [
            'code',
            'link',
            'status',
            'message',
            'callback',
        ];
    }
}
