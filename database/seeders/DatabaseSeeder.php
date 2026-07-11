<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\World;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a previewable demo account.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo Explorer',
            'email' => 'demo@minelog.test',
        ]);

        $survival = $user->worlds()->create([
            'name' => 'Survival Realm',
            'description' => 'Our long-running survival world — bases, deep mines, a nether highway, and a trip to the End.',
            'seed' => '1785523608',
            'is_public' => true,
        ]);

        $this->addWaypoints($survival, [
            ['Diamond cave', 128, -12, -340, 'overworld', 'Big deepslate pocket, 9 diamonds so far. Torches lead back to the rail.', ['mining', 'diamonds'], 'confirmed'],
            ['Home base', 64, 71, 210, 'overworld', 'Main base with the enchanting room and storage. Bed here.', ['base', 'spawn'], 'confirmed'],
            ['Ocean monument', -820, 45, 300, 'overworld', 'Guardian farm candidate. Bring water breathing.', ['monument', 'guardian'], 'confirmed'],
            ['Stronghold portal', 1180, 24, -640, 'overworld', 'End portal room, 3 eyes still needed.', ['stronghold'], 'confirmed'],
            ['Nether fortress', -40, 88, 512, 'nether', 'Blaze spawner in the north wing. Bring fire resistance.', ['fortress', 'blaze'], 'confirmed'],
            ['Bastion remnant', 96, 40, 604, 'nether', 'Hoglin stables. Lots of gold blocks, watch the piglins.', ['bastion', 'loot'], 'confirmed'],
            ['End city', 1024, 62, 980, 'end', 'Elytra secured. Two more ships to the east.', ['elytra', 'loot'], 'confirmed'],
            [null, 902, 63, -1188, 'overworld', null, [], 'draft'],
        ]);

        $creative = $user->worlds()->create([
            'name' => 'Creative Flatlands',
            'description' => 'A private sandbox for testing builds before committing them to the survival world.',
            'seed' => null,
            'is_public' => false,
        ]);

        $this->addWaypoints($creative, [
            ['Castle prototype', 0, 100, 0, 'overworld', 'Gothic build, testing spire proportions.', ['build'], 'confirmed'],
            ['Redstone lab', 48, 100, -30, 'overworld', 'Auto-sorter and hidden door tests.', ['redstone'], 'confirmed'],
        ]);
    }

    /**
     * @param  list<array{0: ?string, 1: int, 2: int, 3: int, 4: string, 5: ?string, 6: list<string>, 7: string}>  $rows
     */
    private function addWaypoints(World $world, array $rows): void
    {
        foreach ($rows as $i => [$name, $x, $y, $z, $dimension, $note, $tags, $status]) {
            $world->waypoints()->create([
                'external_id' => "seed-{$world->id}-{$i}",
                'name' => $name,
                'x' => $x,
                'y' => $y,
                'z' => $z,
                'dimension' => $dimension,
                'note' => $note,
                'tags' => $tags,
                'status' => $status,
                'captured_at' => Carbon::now()->subDays(count($rows) - $i),
            ]);
        }
    }
}
