<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class MainPageController extends Controller
{
    public function show(Request $request)
    {
        return view('mainPage')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'maxReplayID' => $this->globalDataService->calculateMaxReplayNumber(),
            'latestPatch' => $this->globalDataService->getLatestPatch(),
            'latestGameDate' => $this->globalDataService->getLatestGameDate(),
        ]);
    }

    public function showSupport(Request $request)
    {
        return view('communitySupport')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'maxReplayID' => $this->globalDataService->calculateMaxReplayNumber(),
            'latestPatch' => $this->globalDataService->getLatestPatch(),
            'latestGameDate' => $this->globalDataService->getLatestGameDate(),
            'patreonEarnings' => $this->getPatreonEarnings(),
        ]);
    }

    public function getFooterData()
    {
        return [
            'maxReplayID' => $this->globalDataService->calculateMaxReplayNumber(),
            'latestPatch' => $this->globalDataService->getLatestPatch(),
            'latestGameDate' => $this->globalDataService->getLatestGameDate(),
        ];
    }

    public function getHeaderAlertData()
    {
        return $this->globalDataService->getHeaderAlert();
    }

    public function test()
    {
        $exception = 1 / 0;
    }

    public function testJS()
    {
        return view('jsException')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }

    public function getPatreonEarnings(): ?float
    {
        try {
            $response = Http::get('https://www.patreon.com/heroesprofile');

            if (! $response->ok()) {
                return null;
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            $raw = $crawler->filter('span[data-tag="earnings-count"]')->first()?->text(null);

            if (! $raw) {
                return null;
            }

            // Extract numeric value, remove $ and /month etc.
            preg_match('/[\d,.]+/', $raw, $matches);

            return isset($matches[0]) ? floatval(str_replace(',', '', $matches[0])) : null;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch Patreon earnings: '.$e->getMessage());

            return null;
        }
    }
}
