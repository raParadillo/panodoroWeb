<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyLog extends Model
{
    protected $table = 'study_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_date',
        'seconds_studied',
    ];

    protected $casts = [
        'session_date' => 'date:Y-m-d',
        'seconds_studied' => 'integer',
    ];
}
