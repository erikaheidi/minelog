<?php

namespace App\Http\Controllers;

use App\Models\World;
use Illuminate\View\View;

class WorldController extends Controller
{
    public function show(World $world): View
    {
        abort_unless($world->is_public, 404);

        $world->load('user');

        return view('worlds.public', [
            'world' => $world,
            'waypoints' => $world->waypoints()->latest()->get(),
            'markers' => $world->mapMarkers(),
        ]);
    }
}
