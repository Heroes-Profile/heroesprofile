<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DocsController extends Controller
{
    /** Where `api:build-spec` writes. */
    public const SPEC = 'spec/heroesprofile-v1.json';

    public function index()
    {
        return view('api.docs', [
            'authenticated' => Auth::guard('api_web')->check(),
            'spec' => $this->spec(),
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
    private function spec(): ?array
    {
        $path = public_path(self::SPEC);

        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }
}
