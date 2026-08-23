<?php

use App\Http\Controllers\Api\Public\PreMatchController;
use App\Http\Controllers\Api\Public\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy uploader paths
|--------------------------------------------------------------------------
|
| The paths the old API host served, mounted on that host so deployed uploaders
| keep working after DNS moves here. **Aliases, not redirects** — the desktop
| client posts replays through .NET's WebClient, which turns a 30x into a GET and
| drops the body, so a redirect would silently lose every upload.
|
| Only reachable when the request carries `Host: api.heroesprofile.com`, so these
| are inert until DNS is repointed. Nothing here answers on the main site.
|
| The handlers are the same ones `routes/api-public.php` uses. The v1 contract was
| built wire-compatible with these clients on purpose: `parsed` reads the same
| `replayID` query param, and `store` takes the same multipart `file` field that
| `WebClient.UploadFile` sends.
|
| Delete this file once the old clients are gone. `api.legacy.*` in
| RouteServiceProvider::UPLOADER_ROUTES goes with it.
|
*/

/*
| Old base was `https://api.heroesprofile.com/api`.
*/

Route::post('api/upload/heroesprofile/{source}', [UploadController::class, 'store'])
    ->middleware(['throttle:upload', 'throttle:upload-daily'])
    ->name('api.legacy.upload');

Route::get('api/replays/fingerprints/{fingerprint}', [UploadController::class, 'fingerprint'])
    ->middleware('throttle:replay-fingerprints')
    ->name('api.legacy.replays.fingerprint');

/*
| Old base was `https://api.heroesprofile.com/openApi`. Both of these are called
| with a trailing slash by the client; `Request::path()` trims it before matching,
| so one route covers both forms without a redirect.
*/

Route::get('openApi/Replay/Parsed', [UploadController::class, 'parsed'])
    ->middleware('throttle:replay-parsed')
    ->name('api.legacy.replays.parsed');

Route::post('openApi/PreMatch', [PreMatchController::class, 'store'])
    ->middleware('throttle:prematch')
    ->name('api.legacy.prematch');
