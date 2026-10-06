<?php

namespace App\Models;

use App\Enums\ProjectRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    use HasFactory;

    // Private disk: files are only reachable through the download route.
    public const DISK = 'local';

    public const MAX_KILOBYTES = 20480; // 20 MB

    protected $fillable = [
        'project_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected $casts = ['file_size' => 'integer'];

    protected static function booted(): void
    {
        static::deleting(function (ProjectFile $file) {
            Storage::disk(self::DISK)->delete($file->file_path);
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deletableBy(?ProjectRole $role, User $user): bool
    {
        if (! $role) {
            return false;
        }

        return $role->canManage() || ($role->canContribute() && $this->user_id === $user->id);
    }

 
}