<?php

namespace App\Http\Controllers;

use App\Models\World;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $worlds = World::query()
            ->public()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->withCount('waypoints')
            ->with('user')
            ->latest()
            ->get();

        return view('home', ['worlds' => $worlds, 'q' => $q]);
    }
}
