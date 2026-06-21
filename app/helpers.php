<?php

use App\Services\TallySheetSessionService;

if (! function_exists('tally_session()')) {
    function tally_session()
    {
        return app(TallySheetSessionService::class);
    }
}

if (! function_exists('app_version')) {
    function app_version(): ?string
    {
        try {
            $composer = json_decode(file_get_contents(base_path('composer.json')), true);

            return $composer['version'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }
}
