<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'task_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'is_checked',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'created_at' => 'datetime',
    ];
}
