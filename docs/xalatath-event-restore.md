# Restoring the live Xal'atath event

Commit `e04fac99` on `SiteRedesign` ("Remove the live Xal'atath event") removed the live event. Its parent `dd7b9adf` is the last commit with the event in place. Everything below comes back out of git. Nothing was dropped from the database.

## Quickest way back

Revert the commit on whatever branch needs it:

```bash
git revert e04fac99
```

Expect conflicts in `layouts/app.blade.php`, `nav.blade.php`, `MainPage.vue` and `app.css`, because the redesign rewrites those files. In each conflict keep the redesign markup, then put the event hooks listed below back into it.

## Pulling pieces back individually

Deleted files come back as they were:

```bash
git checkout dd7b9adf -- app/Http/Controllers/XalatathEventController.php app/Models/XalatathData.php resources/js/pageActivity.js resources/js/components/Event/XalatathScoreboard.vue resources/js/components/Event/VoidGlitch.vue resources/js/components/Event/VoidWhispers.vue resources/js/components/Event/VoidStageUp.vue resources/js/components/Event/VoidFallenSplash.vue resources/js/components/Event/VoidHiddenEye.vue resources/js/components/Event/VoidEyeFound.vue resources/js/components/Event/XalatathFinalBanner.vue resources/js/components/Event/VoidEndingSplash.vue
```

Components in `components/` are auto-registered by filename, so the Vue files need no other registration.

To see exactly what was cut from each edited file:

```bash
git show e04fac99 -- <path>
```

Hand-merge the edited files below, since the redesign will have changed them.

| File | What to put back |
|---|---|
| `app/Services/GlobalDataService.php` | `getXalatathThresholds`, `getXalatathEvent`, `getXalatathEnding`, the `?void_stage` / `?void_ended` local previews (`getXalatathPreviewStage`, `getXalatathEndedPreview`, plus the preview branch at the top of `isXalatathRecapAvailable`), `awardVoidEye`, `voidEyeVisitorKey`, `getVoidEyeSpot`, `fakeXalatathTotals`; the `XalatathData` and `Crypt` imports; blade globals `xalatathEvent`, `voidEyeSpot` and `xalatathEnding` in `getBladeGlobals()` |
| `routes/web.php` | `GET /Event/Xalatath/Totals` → `totals`, `POST /Event/Xalatath/Eye` → `claimEye`. `/Flair/State` now lives in `FlairController::state`, so delete `flairState` from the restored `XalatathEventController` rather than having two |
| `app/Providers/RouteServiceProvider.php` | Throttles `xalatath-totals` and `xalatath-eye` (10/min each, keyed by user or IP) |
| `app/Http/Controllers/Auth/BattleNetController.php` | After login: pull `void_eye_pending` from the session, call `awardVoidEye`, flash `void_eye_awarded` |
| `app/Providers/ViewServiceProvider.php` | `$view->with('void_corruption_optout', request()->cookie('void_corruption_optout') === '1')` |
| `app/Http/Middleware/EncryptCookies.php` | `void_corruption_optout` in `$except` (the scoreboard sets it from JS) |
| `app/Http/Controllers/UploadController.php` `embed()` | Compute `voidStage` (null outside the event, 0 when opted out) and pass it to `upload-embed` |
| `resources/views/upload-embed.blade.php` and `ReplayUploaderEmbed.vue` | The `voidStage` prop, the stage logo, and the stage 1 glow / stage 4 card palette (`cardStyle`) |
| `resources/views/layouts/app.blade.php` | The `@php` block setting `$xalatathEvent`, `$voidOptOut`, `$voidStage`, `$siteLogo`, `$siteIcon`, `$navLogo`; the stage-logo favicons with `data-void-favicon`; the `void-stage-N` body class; the scoreboard, glitch, fallen-splash and stage-up block; the final-banner and ending-splash block; the hidden-eye and eye-found blocks; the footer logo `js-void-logo` |
| `resources/views/layouts/embed.blade.php` | The `void-stage-N` body class |
| `resources/views/nav.blade.php` | Both nav logos using `$navLogo` with `js-void-logo`, and `<void-whispers>` at the top of the alt-account nav |
| `resources/views/mainPage.blade.php` and `MainPage.vue` | The `xalatathEvent`, `voidStage` and `xalatathEnding` props, the "Heroes [stage logo] Profile" heading, and the recap link |
| `resources/js/app.js` | `import './pageActivity';` (the hidden eye waits on `whenPageSettled`) |
| `resources/css/app.css` | Hidden-eye, eye-found and eye-claiming rules; stage-up popup; fallen splash; the `void-stage-1..5` glow and palette rules; `.void-glyph-overlay` |
| `config/global.php` + `.env.example` | `xalatath_event.enabled`, `stage_thresholds`, `eye_every_page`, `recap_days`, and env vars `XALATATH_EVENT_ENABLED`, `XALATATH_EVENT_STAGE_THRESHOLDS`, `XALATATH_EVENT_RECAP_DAYS`, `XALATATH_EYE_EVERY_PAGE` |

## Never removed

- The recap page (`XalatathRecapController`, `event/xalatath-recap.blade.php`, `XalatathRecap.vue`), share cards and their bucket
- The tile, whisper and glyph helpers
- The `xalatath_recap` table, plus `starts_at` / `ends_at` config
- Eye flair: `VoidEyeFlair.vue`, `voidEye.js`, `battlenet_account_flair`, the Profile Settings toggle, `getVoidEyeHolders`
- The event images in `public/images/event/xalatath/`

## Data and infrastructure

- `heroesprofile.xalatath_data` still exists. It is on the sunset list, not dropped. Live totals came from it, so whatever wrote it (the parser or a VM job) has to be running again before a restored scoreboard shows anything.
- A new event also needs a fresh `ends_at` and thresholds. Clear `ends_at` too: `getXalatathEvent` returns null once `ends_at` has passed.
- Set `XALATATH_EVENT_ENABLED=true` on the Cloud Run services (prod, dev and redesign) to turn it on.
