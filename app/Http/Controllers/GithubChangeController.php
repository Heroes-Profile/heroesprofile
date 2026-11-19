<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
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

    /** Part of every cache key here; bump it when what's cached changes so old entries are ignored. */
    private const CACHE_VERSION = 'v2';

    /** A commit never changes, so its line and file counts are kept a long time. */
    private const STATS_CACHE_SECONDS = 2592000;

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
     * shows, with each commit's line and file counts. Null when GitHub could not be
     * reached. Everything is fetched anonymously: the repo is public.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function commits(string $branch): ?array
    {
        $key = 'github_commits_'.self::CACHE_VERSION.'|'.$branch;

        if (Cache::has($key)) {
            $commits = Cache::get($key);
        } else {
            $commits = $this->fetch($branch);

            Cache::put($key, $commits, $commits === null ? self::FAILURE_CACHE_SECONDS : self::CACHE_SECONDS);
        }

        if ($commits === null) {
            return null;
        }

        $stats = $this->stats(array_column($commits, 'sha'));

        return array_map(fn (array $commit) => [
            ...$commit,
            ...($stats[$commit['sha']] ?? ['additions' => null, 'deletions' => null, 'files' => null]),
        ], $commits);
    }

    private function fetch(string $branch): ?array
    {
        $repo = config('services.github.repo');

        try {
            $response = Http::acceptJson()->timeout(5)
                ->get("https://api.github.com/repos/{$repo}/commits", ['sha' => $branch]);
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

    /**
     * Lines added and removed and files changed for each commit. The commit list
     * doesn't include them, so each commit not yet cached is fetched on its own,
     * all at once. A commit GitHub doesn't answer for is tried again on a later
     * view, but not for a minute after a failure, so an outage or the anonymous
     * rate limit isn't hit on every page view.
     *
     * @param  array<int, string>  $shas
     * @return array<string, array{additions: int, deletions: int, files: int}>
     */
    private function stats(array $shas): array
    {
        $stats = [];
        $missing = [];

        foreach (array_unique($shas) as $sha) {
            $cached = Cache::get('github_commit_stats_'.self::CACHE_VERSION.'|'.$sha);

            if ($cached !== null) {
                $stats[$sha] = $cached;
            } else {
                $missing[] = $sha;
            }
        }

        if ($missing === [] || Cache::has('github_commit_stats_paused_'.self::CACHE_VERSION)) {
            return $stats;
        }

        $repo = config('services.github.repo');

        try {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn (string $sha) => $pool->as($sha)->acceptJson()->timeout(5)
                    ->get("https://api.github.com/repos/{$repo}/commits/{$sha}"),
                $missing
            ));
        } catch (Throwable $e) {
            report($e);
            Cache::put('github_commit_stats_paused_'.self::CACHE_VERSION, true, self::FAILURE_CACHE_SECONDS);

            return $stats;
        }

        foreach ($missing as $sha) {
            $response = $responses[$sha] ?? null;

            if (! $response instanceof Response || ! $response->successful()) {
                Cache::put('github_commit_stats_paused_'.self::CACHE_VERSION, true, self::FAILURE_CACHE_SECONDS);

                continue;
            }

            $commit = $response->json();
            $stats[$sha] = [
                'additions' => (int) ($commit['stats']['additions'] ?? 0),
                'deletions' => (int) ($commit['stats']['deletions'] ?? 0),
                'files' => count($commit['files'] ?? []),
            ];
            Cache::put('github_commit_stats_'.self::CACHE_VERSION.'|'.$sha, $stats[$sha], self::STATS_CACHE_SECONDS);
        }

        return $stats;
    }
}
