<?php

namespace App\Console\Commands;

use App\Models\Hero;
use App\Models\Map;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate public/sitemap.xml from current database content';

    private string $baseUrl = 'https://www.heroesprofile.com';

    public function handle(): void
    {
        $now = Carbon::now()->toAtomString();

        $urls = array_merge(
            $this->staticPages($now),
            $this->heroPages($now),
            $this->mapPages($now),
        );

        $xml = $this->buildXml($urls);

        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->info('Sitemap generated with '.count($urls).' URLs.');
    }

    private function staticPages(string $now): array
    {
        return [
            // High-traffic global stats pages — update frequently
            ['loc' => '/Global/Hero',              'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '1.0'],
            ['loc' => '/Global/Matchups',          'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '1.0'],
            ['loc' => '/Global/Matchups/Talents',  'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => '/Global/Talents',           'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.9'],
            ['loc' => '/Global/Talents/Builder',   'lastmod' => $now, 'changefreq' => 'weekly',  'priority' => '0.8'],
            ['loc' => '/Global/Hero/Maps',         'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.8'],
            ['loc' => '/Global/Compositions',      'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.8'],
            ['loc' => '/Global/Draft',             'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.8'],
            ['loc' => '/Global/Party',             'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.7'],
            ['loc' => '/Global/Leaderboard',       'lastmod' => $now, 'changefreq' => 'daily',   'priority' => '0.9'],

            // Static informational pages — rarely change
            ['loc' => '/',                         'lastmod' => $now, 'changefreq' => 'weekly',  'priority' => '1.0'],
            ['loc' => '/FAQ',                      'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => '/Contact',                  'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => '/Privacy/Policy',           'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.3'],
        ];
    }

    private function heroPages(string $now): array
    {
        $heroes = Hero::orderBy('name', 'ASC')->pluck('name');
        $urls = [];

        foreach ($heroes as $name) {
            $encoded = rawurlencode($name);
            $urls[] = ['loc' => "/Global/Talents/{$encoded}",        'lastmod' => $now, 'changefreq' => 'daily',  'priority' => '0.8'];
            $urls[] = ['loc' => "/Global/Hero/Maps/{$encoded}",      'lastmod' => $now, 'changefreq' => 'daily',  'priority' => '0.7'];
            $urls[] = ['loc' => "/Global/Matchups/{$encoded}",       'lastmod' => $now, 'changefreq' => 'daily',  'priority' => '0.7'];
            $urls[] = ['loc' => "/Global/Talents/Builder/{$encoded}", 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.6'];
        }

        return $urls;
    }

    private function mapPages(string $now): array
    {
        $maps = Map::where('playable', 1)->orderBy('name', 'ASC')->pluck('name');
        $urls = [];

        foreach ($maps as $name) {
            $encoded = rawurlencode($name);
            $urls[] = ['loc' => "/Global/Hero/Maps/{$encoded}", 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.6'];
        }

        return $urls;
    }

    private function buildXml(array $urls): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($urls as $url) {
            $loc = htmlspecialchars($this->baseUrl.$url['loc'], ENT_XML1);
            $lines[] = '  <url>';
            $lines[] = "    <loc>{$loc}</loc>";
            $lines[] = "    <lastmod>{$url['lastmod']}</lastmod>";
            $lines[] = "    <changefreq>{$url['changefreq']}</changefreq>";
            $lines[] = "    <priority>{$url['priority']}</priority>";
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
