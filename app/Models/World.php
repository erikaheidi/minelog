<?php

namespace App\Models;

use Database\Factories\WorldFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $seed
 * @property bool $is_public
 */
class World extends Model
{
    /** @use HasFactory<WorldFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name', 'slug', 'description', 'seed', 'is_public',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (World $world): void {
            if (blank($world->slug)) {
                $world->slug = $world->uniqueSlug($world->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Waypoint, $this>
     */
    public function waypoints(): HasMany
    {
        return $this->hasMany(Waypoint::class);
    }

    /**
     * @param  Builder<World>  $query
     */
    #[Scope]
    protected function public(Builder $query): void
    {
        $query->where('is_public', true);
    }

    /**
     * The distinct dimensions that have at least one waypoint in this world.
     *
     * @return list<string>
     */
    public function dimensionsPresent(): array
    {
        return array_values(
            $this->waypoints()
                ->select('dimension')
                ->distinct()
                ->get()
                ->map(fn (Waypoint $waypoint): string => $waypoint->dimension)
                ->all()
        );
    }

    /**
     * Marker payload for the Leaflet map (only waypoints that have coordinates).
     *
     * @return list<array<string, mixed>>
     */
    public function mapMarkers(): array
    {
        return array_values(
            $this->waypoints()
                ->whereNotNull('x')
                ->whereNotNull('z')
                ->get()
                ->map(fn (Waypoint $w): array => [
                    'name' => $w->name ?: 'Unnamed',
                    'x' => $w->x,
                    'y' => $w->y,
                    'z' => $w->z,
                    'dimension' => $w->dimension,
                    'color' => $w->dimensionColor(),
                    'note' => $w->note,
                    'shot' => $w->screenshot_path ? Storage::url($w->screenshot_path) : null,
                ])
                ->all()
        );
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'world';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
