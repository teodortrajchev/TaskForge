<?php

namespace App\Models;

use App\Notifications\HasDueReminders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, HasDueReminders;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    protected static function booted(): void
        {
            // Stamp the finish time whenever status flips, whichever code path changed it.
            static::saving(function (Task $task) {
                if ($task->isDirty('status')) {
                    $task->completed_at = $task->status === 'completed' ? now() : null;
                }
            });
        }
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}