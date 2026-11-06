<?php

namespace App\Http\Middleware;

use App\Enums\Deadline;
use App\Services\Deadlines;
use Closure;
use Illuminate\Http\Request;

class CheckRegisterBonus
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // A deadline without date stays open (Carbon 3 no longer compares a date with null)
        abort_unless(app(Deadlines::class)->isOpen(Deadline::Bonus), 404);

        return $next($request);
    }
}
