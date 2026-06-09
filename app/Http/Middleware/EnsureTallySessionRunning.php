<?php

namespace App\Http\Middleware;

use App\Services\TallySheetSessionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTallySessionRunning
{
    public function __construct(private readonly TallySheetSessionService $tallySheetSessionService) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tallySheetSessionService->isRunning()) {
            return redirect()->route('tally-sheet.start-session');
        }

        return $next($request);
    }
}
