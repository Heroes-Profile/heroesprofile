<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GithubChangeController extends Controller
{
    private const BRANCHES = ['master', 'develop'];

    private const CACHE_SECONDS = 3600;

    /** Short, so a GitHub outage clears itself instead of sticking for an hour. */
    private const FAILURE_CACHE_SECONDS = 60;

    public function show(Request $request)
    {
        $commits = [];

        foreach (self::BRANCHES as $branch) {
            $commits[$branch] = $this->commits($branch);
        }

        return view('githubChanges')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'masterCommits' => $commits['master'],
            'developCommits' => $commits['develop'],
        ]);
    }

    /**
     * The branch's recent commits, fetched server-side and trimmed to what the page
     * shows. Null when GitHub could not be reached.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function commits(string $branch): ?array
    {
        $key = 'github_commits|'.$branch;

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $commits = $this->fetch($branch);

        Cache::put($key, $commits, $commits === null ? self::FAILURE_CACHE_SECONDS : self::CACHE_SECONDS);

        return $commits;
    }

    private function fetch(string $branch): ?array
    {
        $repo = config('services.github.repo');

        $url = "https://api.github.com/repos/{$repo}/commits";
        $token = config('services.github.token');

        try {
            $response = Http::acceptJson()->timeout(5)
                ->when($token, fn ($request) => $request->withToken($token))
                ->get($url, ['sha' => $branch]);

            // A revoked or expired token must not take the page down: the repo is
            // public, so fall back to an anonymous call.
            if ($token && $response->status() === 401) {
                Log::warning('GitHub rejected services.github.token; fetching commits anonymously. Remove or replace GITHUB_PERSONAL_ACCESS_TOKEN.');

                $response = Http::acceptJson()->timeout(5)->get($url, ['sha' => $branch]);
            }
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        if (! $response->successful() || ! is_array($response->json())) {
            Log::warning('GitHub commits fetch failed', ['branch' => $branch, 'status' => $response->status()]);

            return null;
        }

        return array_map(fn (array $commit) => [
            'sha' => $commit['sha'],
            'short_sha' => substr($commit['sha'], 0, 7),
            'url' => "https://github.com/{$repo}/commit/{$commit['sha']}",
            'message' => $commit['commit']['message'] ?? '',
            'date' => $commit['commit']['author']['date'] ?? null,
            // Null when the commit's email is not linked to a GitHub account.
            'author_login' => $commit['author']['login'] ?? null,
            'author_name' => $commit['commit']['author']['name'] ?? null,
        ], $response->json());
    }
}
