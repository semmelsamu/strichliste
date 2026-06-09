<?php

use App\Services\TallySheetSessionService;

if (! function_exists('tally_session')) {
    function tally_session(?string $key = null)
    {
        return app(TallySheetSessionService::class)->get($key);
    }
}
