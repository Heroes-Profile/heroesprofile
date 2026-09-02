<?php

namespace App\Http;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\BlockBannedIPs;
use App\Http\Middleware\BlockSuspendedApiAccount;
use App\Http\Middleware\CheckIfPatreonSupporter;
use App\Http\Middleware\CheckIfPrivateProfilePage;
use App\Http\Middleware\CommunitySupportRedirect;
use App\Http\Middleware\ConvertResponseToCsv;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnforceApiQuota;
use App\Http\Middleware\EnsureApiAccountAuthenticated;
use App\Http\Middleware\EnsureApiAdmin;
use App\Http\Middleware\EnsureBattlenetAuthenticated;
use App\Http\Middleware\LogIPAndUserAgent;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequireApiTermsAcceptance;
use App\Http\Middleware\RequireNgsAccess;
use App\Http\Middleware\RequireWebsiteAuthForAll;
use App\Http\Middleware\ResolveApiKey;
use App\Http\Middleware\ServeApiFixtures;
use App\Http\Middleware\SetGlobalDataValues;
use App\Http\Middleware\ThrottleNonApiRequests;
use App\Http\Middleware\ThrottleOldReplayRequests;
use App\Http\Middleware\TrackSlowRequests;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateApiPostOrigin;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCloudTasksRequest;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\VerifyStripeWebhookSecretConfigured;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        BlockBannedIPs::class,
        // Ahead of the throttle exemption below, so an unverifiable webhook is
        // refused rather than merely let through unthrottled.
        VerifyStripeWebhookSecretConfigured::class,
        ThrottleNonApiRequests::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        SetGlobalDataValues::class,
        ThrottleOldReplayRequests::class,
        TrackSlowRequests::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            CheckIfPatreonSupporter::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ValidateApiPostOrigin::class,
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
        ],

        /*
         * External API. Stateless on purpose: no session, no CSRF, no
         * CheckIfPatreonSupporter, and no ValidateApiPostOrigin — that one 403s
         * anything without a heroesprofile Origin, which is every third-party
         * client there is.
         */
        'api.external' => [
            ResolveApiKey::class,
            ThrottleRequests::class.':api-external',
            SubstituteBindings::class,
            // Last in the group, so on the way back it runs before the others but
            // still outside the route middleware — which is what matters, since it
            // has to see fixture output as well as live output.
            ConvertResponseToCsv::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'precognitive' => HandlePrecognitiveRequests::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'ensureBattlenetAuth' => EnsureBattlenetAuthenticated::class,
        'ensureApiAccountAuth' => EnsureApiAccountAuthenticated::class,
        'ensureApiAdmin' => EnsureApiAdmin::class,
        'requireApiTerms' => RequireApiTermsAcceptance::class,
        'blockSuspendedApi' => BlockSuspendedApiAccount::class,
        'api.quota' => EnforceApiQuota::class,
        'api.fixtures' => ServeApiFixtures::class,
        'api.ngs' => RequireNgsAccess::class,
        'checkIfPrivateProfilePage' => CheckIfPrivateProfilePage::class,
        'logIpAndUserAgent' => LogIPAndUserAgent::class,
        'communitySupportRedirect' => CommunitySupportRedirect::class,
        'requireWebsiteAuthForAll' => RequireWebsiteAuthForAll::class,
        'cloud.tasks' => VerifyCloudTasksRequest::class,
    ];
}
