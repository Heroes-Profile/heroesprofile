<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiVariables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DocsController extends Controller
{
    /** Where `api:build-spec` writes. */
    public const SPEC = 'spec/heroesprofile-v1.json';

    /** The admin-only paths. Outside public/, so it is never served as a file. */
    public static function adminSpecPath(): string
    {
        return resource_path('spec/heroesprofile-v1-admin.json');
    }

    public function index()
    {
        $account = Auth::guard('api_web')->user();

        return view('api.docs', [
            'authenticated' => $account !== null,
            'spec' => $this->spec($account?->actingAsAdmin() ?? false),
            // Alongside the endpoints rather than on a page of its own: the values a
            // parameter accepts are wanted while reading the endpoint that takes it.
            // Heroes, maps and patches change with a release, not between page loads.
            'variables' => Cache::remember('api_docs_variables', 3600, fn () => ApiVariables::all()),
            // Admins get the data switch and the admin toggle inline, so checking
            // an endpoint against live data does not mean a trip to the account
            // page and back.
            'account' => $account === null ? null : [
                'admin' => $account->isAdmin(),
                'admin_mode' => $account->actingAsAdmin(),
                'test_mode' => $account->inTestMode(),
                'receives_test_data' => $account->receivesTestData(),
            ],
        ]);
    }

    /**
     * The generated document, or null if it has not been built.
     *
     * Read from disk rather than regenerated per request: it is a build artifact,
     * and building it walks every route and reads every fixture.
     *
     * @return array<string, mixed>|null
     */
    private function spec(bool $withAdmin): ?array
    {
        $spec = $this->readJson(public_path(self::SPEC));

        if ($spec === null || ! $withAdmin) {
            return $spec;
        }

        foreach ($this->readJson(self::adminSpecPath())['paths'] ?? [] as $path => $operations) {
            $spec['paths'][$path] = array_merge($spec['paths'][$path] ?? [], $operations);
        }

        return $spec;
    }

    /** @return array<string, mixed>|null */
    private function readJson(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }
}
