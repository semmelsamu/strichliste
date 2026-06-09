<?php

use App\Services\TallySheetSessionService;

if (! function_exists('tally_session()')) {
    function tally_session()
    {
        return app(TallySheetSessionService::class);
    }
}
