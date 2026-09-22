@component('mail::message')
# Twitch extension is out of date

A live game on **{{ $channel }}** used things extension {{ $releasedVersion ?? '(unknown version)' }} does not bundle. Viewers see them as not available until the next release.

@if ($heroes)
**Heroes**

@foreach ($heroes as $hero)
- {{ $hero }}
@endforeach
@endif

@if ($talents)
**Talents**

@foreach ($talents as $talent)
- {{ $talent }}
@endforeach
@endif

To fix: run `php artisan twitch:game-data` here, then `npm run release` in the extension, commit the manifest it writes, and upload the zip to Twitch.

Each missing hero or talent is reported once per extension version.
@endcomponent
