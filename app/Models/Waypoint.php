<?php

namespace App\Models;

use Database\Factories\WaypointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $world_id
 * @property string|null $external_id
 * @property string|null $name
 * @property int|null $x
 * @property int|null $y
 * @property int|null $z
 * @property string $dimension
 * @property string|null $note
 * @property array<int, string>|null $tags
 * @property \Illuminate\Support\Carbon|null $captured_at
 * @property string|null $screenshot_path
 * @property string $status
 */
class Waypoint extends Model
{
    /** @use HasFactory<WaypointFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'world_id', 'external_id', 'name', 'x', 'y', 'z', 'dimension', 'note', 'tags',
        'captured_at', 'screenshot_path', 'status',
    ];

    public const DIMENSIONS = ['overworld', 'nether', 'end'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'captured_at' => 'datetime',
            'x' => 'integer',
            'y' => 'integer',
            'z' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<World, $this>
     */
    public function world(): BelongsTo
    {
        return $this->belongsTo(World::class);
    }

    public function hasCoords(): bool
    {
        return $this->x !== null && $this->y !== null && $this->z !== null;
    }

    public function coordString(): string
    {
        return $this->hasCoords() ? "{$this->x}, {$this->y}, {$this->z}" : '—';
    }

    public function dimensionColor(): string
    {
        return match ($this->dimension) {
            'nether' => '#b3312c',
            'end' => '#7b6ca8',
            default => '#4a8c3f',
        };
    }
}
