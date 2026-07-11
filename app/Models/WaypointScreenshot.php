<?php

namespace App\Models;

use Database\Factories\WaypointScreenshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $waypoint_id
 * @property string $disk
 * @property string $path
 */
class WaypointScreenshot extends Model
{
    /** @use HasFactory<WaypointScreenshotFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'waypoint_id', 'disk', 'path',
    ];

    /**
     * @return BelongsTo<Waypoint, $this>
     */
    public function waypoint(): BelongsTo
    {
        return $this->belongsTo(Waypoint::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Remove the underlying file from its disk. Called explicitly, since a
     * database-level cascade delete bypasses Eloquent model events.
     */
    public function deleteFile(): void
    {
        Storage::disk($this->disk)->delete($this->path);
    }
}
