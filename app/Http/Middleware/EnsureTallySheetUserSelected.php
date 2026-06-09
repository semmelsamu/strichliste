<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use App\Services\TallySheetSessionService;
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
        $user = app(TallySheetSessionService::class)->get('user');

        if (! $user || $user->type != UserType::NormalUser) {
            app(TallySheetSessionService::class)->logout();

            return redirect()->route('tally-sheet.auth.list-users');
        }

        return $next($request);
    }
}
