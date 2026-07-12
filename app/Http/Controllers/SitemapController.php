<?php

namespace App\Http\Controllers;

use App\Models\World;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $worlds = World::query()
            ->public()
            ->latest('updated_at')
            ->get();

        return response()
            ->view('sitemap', ['worlds' => $worlds])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow:',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain');
    }
}
