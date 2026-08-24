<?php

namespace MityDigital\FuseUtilities\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ContentWithPathCommand extends Command
{
    protected $signature = 'fuse:content-with-path
                            {path=temp/ : The path to search for}';

    protected $description = 'Get a list of Statamic content that has a reference to a specific path.';

    public function handle(): int
    {
        $path = $this->argument('path');

        $process = new Process([
            'git',
            'ls-files',
            '--cached',
            '--others',
            '--exclude-standard',
            '-z',
        ], base_path());

        $process->run();

        if (! $process->isSuccessful()) {
            $this->components->error('Unable to get the project files from Git.');

            return self::FAILURE;
        }

        $files = array_filter(
            explode("\0", $process->getOutput())
        );

        $matches = collect();

        foreach ($files as $file) {
            $fullPath = base_path($file);

            if (! is_file($fullPath) || ! is_readable($fullPath)) {
                continue;
            }

            $contents = file_get_contents($fullPath);

            if ($contents === false || str_contains($contents, "\0")) {
                continue;
            }

            $lines = preg_split('/\R/', $contents);

            $lineMatches = collect($lines)
                ->map(function (string $line, int $index) use ($path) {
                    if (! str_contains($line, $path)) {
                        return null;
                    }

                    return [
                        'line' => $index + 1,
                        'content' => trim($line),
                    ];
                })
                ->filter()
                ->values();

            if ($lineMatches->isNotEmpty()) {
                $matches->put($file, $lineMatches);
            }
        }

        if ($matches->isEmpty()) {
            $this->components->error(
                sprintf('No files found containing "%s".', $path)
            );

            return self::FAILURE;
        }

        foreach ($matches->sortKeys() as $file => $lineMatches) {
            $this->line("<fg=cyan;options=bold>{$file}</>");

            foreach ($lineMatches as $match) {
                $lineNumber = str_pad(
                    (string) $match['line'],
                    strlen((string) $lineMatches->max('line')),
                    ' ',
                    STR_PAD_LEFT
                );

                $content = $this->escapeConsoleOutput($match['content']);

                $this->line(
                    "    <fg=yellow>{$lineNumber}:</> <fg=gray>{$content}</>"
                );
            }

            $this->newLine();
        }

        $count = $matches->count();

        $this->components->info(
            sprintf(
                '%d %s found containing "%s".',
                $count,
                str('file')->plural($count),
                $path,
            )
        );

        return self::SUCCESS;
    }

    protected function escapeConsoleOutput(string $value): string
    {
        return str_replace(
            ['<', '>'],
            ['\<', '\>'],
            $value
        );
    }
}
