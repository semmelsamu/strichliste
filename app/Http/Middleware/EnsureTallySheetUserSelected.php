<?php

namespace App\Http\Middleware;

use App\TallySheetSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTallySheetUserSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(TallySheetSession::class)->currentUser()) {
            return redirect()->route('tally-sheet.auth.list-users');
        }

        return $next($request);
    }
}
