<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action_type',
        'description',
        'related_id',
        'created_at',
    ];

    protected $casts = [
        'related_id' => 'integer',
        'created_at' => 'datetime',
    ];
}
