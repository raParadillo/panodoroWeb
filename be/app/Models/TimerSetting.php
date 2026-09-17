<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimerSetting extends Model
{
    protected $table = 'timer_settings';
    protected $primaryKey = 'setting_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'study_minutes',
        'break_minutes',
        'total_loops',
        'updated_at',
    ];

    protected $casts = [
        'study_minutes' => 'integer',
        'break_minutes' => 'integer',
        'total_loops' => 'integer',
        'updated_at' => 'datetime',
    ];
}
