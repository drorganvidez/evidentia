<?php

namespace App\Http;

use App\Http\Middleware\MeetingMinutesMine;
use App\Http\Middleware\MeetingRequestMine;
use App\Http\Middleware\SignatureSheetMine;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\SelectDatabase::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SelectDatabaseApi::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'checknotnull' => \App\Http\Middleware\CheckNotNull::class,
        'checkroles' => \App\Http\Middleware\CheckRoles::class,
        'evidencecanbeedited' => \App\Http\Middleware\EvidenceCanBeEdited::class,
        'evidencemine' => \App\Http\Middleware\EvidenceMine::class,
        'evidencefrommycommittee' => \App\Http\Middleware\EvidenceFromMyComittee::class,
        'checkblock' => \App\Http\Middleware\CheckBlock::class,
        'checkuploadevidences' => \App\Http\Middleware\CheckUploadEvidences::class,
        'checkvalidateevidences' => \App\Http\Middleware\CheckValidateEvidences::class,
        'checkregistermeetings' => \App\Http\Middleware\CheckRegisterMeeting::class,
        'checkregisterbonus' => \App\Http\Middleware\CheckRegisterBonus::class,
        'checkregistereventsandattendings' => \App\Http\Middleware\CheckRegisterEventsAndAttendings::class,
        'checkproofdownload' => \App\Http\Middleware\CheckProofDownload::class,
        'checkisadministrator' => \App\Http\Middleware\CheckIsAdministrator::class,
        'meetingrequestmine' => MeetingRequestMine::class,
        'signaturesheetmine' => SignatureSheetMine::class,
        'meetingminutesmine' => MeetingMinutesMine::class,
        'jwt.verify' => \App\Http\Middleware\JwtMiddleware::class,
    ];
}
