<?php

namespace App\Services;

use App\Models\Waypoint;
use App\Models\World;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class WaypointImporter
{
    /**
     * Map the values the add-on may emit onto Minelog's dimension names.
     */
    private const DIMENSION_ALIASES = [
        'overworld' => 'overworld',
        'nether' => 'nether',
        'end' => 'end',
        'minecraft:overworld' => 'overworld',
        'minecraft:nether' => 'nether',
        'minecraft:the_end' => 'end',
    ];

    /**
     * Import a JSON export (from the in-game `!wp export` command) into a world.
     *
     * Waypoints are upserted by their in-game id (scoped to the world), so
     * re-importing the same export updates existing rows instead of duplicating.
     *
     * @return array{created: int, updated: int, skipped: int}
     *
     * @throws ValidationException when the payload is not a JSON array of waypoints
     */
    public function importForWorld(World $world, string $json): array
    {
        $decoded = json_decode(trim($json), true);

        if (! is_array($decoded) || ! array_is_list($decoded)) {
            throw ValidationException::withMessages([
                'payload' => 'That does not look like a Minelog export. Paste the JSON line printed by "!wp export".',
            ]);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($decoded as $entry) {
            $normalized = $this->normalize($entry);

            if ($normalized === null) {
                $skipped++;

                continue;
            }

            $externalId = $normalized['external_id'];
            unset($normalized['external_id']);

            $waypoint = Waypoint::updateOrCreate(
                ['world_id' => $world->id, 'external_id' => $externalId],
                $normalized,
            );

            $waypoint->wasRecentlyCreated ? $created++ : $updated++;
        }

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * @param  mixed  $entry
     * @return array<string, mixed>|null null when the entry can't be imported
     */
    private function normalize($entry): ?array
    {
        if (! is_array($entry)) {
            return null;
        }

        $externalId = isset($entry['id']) ? (string) $entry['id'] : null;
        if ($externalId === null || $externalId === '') {
            return null;
        }

        if (! isset($entry['x'], $entry['y'], $entry['z']) || ! is_numeric($entry['x']) || ! is_numeric($entry['y']) || ! is_numeric($entry['z'])) {
            return null;
        }

        $label = isset($entry['label']) ? trim((string) $entry['label']) : '';
        $dimension = self::DIMENSION_ALIASES[strtolower((string) ($entry['dimension'] ?? ''))] ?? 'overworld';

        return [
            'external_id' => $externalId,
            'name' => $label !== '' ? $label : null,
            'x' => (int) round((float) $entry['x']),
            'y' => (int) round((float) $entry['y']),
            'z' => (int) round((float) $entry['z']),
            'dimension' => $dimension,
            'captured_at' => $this->parseTimestamp($entry['capturedAt'] ?? null),
            'status' => $label !== '' ? 'confirmed' : 'draft',
        ];
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
