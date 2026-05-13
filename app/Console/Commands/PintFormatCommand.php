<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PintFormatCommand extends Command
{
    protected $signature = 'pint:format';

    protected $description = 'Format PHP code from stdin using Pint and write the result to stdout. Used as an external formatter by Zed.';

    public function handle(): int
    {
        $content = file_get_contents('php://stdin');
        $tempFile = tempnam(sys_get_temp_dir(), 'pint').'.php';

        try {
            file_put_contents($tempFile, $content);

            $process = new Process([base_path('vendor/bin/pint'), '--quiet', $tempFile]);
            $process->run();

            $this->output->write(file_get_contents($tempFile));
        } finally {
            unlink($tempFile);
        }

        return self::SUCCESS;
    }
}
