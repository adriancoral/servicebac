<?php

namespace App\Models;

use App\Events\PdfWorkCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed code
 * @property mixed payload
 * @method static findOrFail($workCode)
 * @method static create(array $array)
 */
class PdfWork extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => PdfWorkCreated::class,
    ];
}
