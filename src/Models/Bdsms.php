<?php


namespace RmdMostakim\BdSms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Bdsms extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'driver',
        'to',
        'message',
        'status',
        'sent_at',
        'error_message',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
        });
    }
}
