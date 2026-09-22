<?php

use App\Http\Controllers\Twitch\BroadcasterController;
use App\Http\Controllers\Twitch\PushController;
use App\Http\Controllers\Twitch\UploaderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Twitch Extension Routes
|--------------------------------------------------------------------------
|
| Mounted at api/twitch/v1 with the stateless `twitch` group. Kept apart from
| routes/api-external.php so the public API's key guard, quota, fixtures and spec
| build never see them.
|
| There is deliberately no route for viewers. Live data reaches them through
| Twitch (Extension PubSub and the configuration segment), so audience size never
| becomes load here.
|
*/

Route::middleware('twitch.uploader')->prefix('uploader')->group(function () {
    Route::get('whoami', [UploaderController::class, 'whoami']);
    Route::post('snapshot', [UploaderController::class, 'snapshot'])->middleware('throttle:twitch-snapshot');
});

Route::get('broadcaster/status', [BroadcasterController::class, 'status'])
    ->middleware(['twitch.jwt:broadcaster', 'throttle:twitch-broadcaster']);

Route::post('internal/push', [PushController::class, 'push'])->middleware('cloud.tasks');
