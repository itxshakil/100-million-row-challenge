<?php

namespace App;

use Exception;
use RuntimeException;

final class Parser
{
    /**
     * @throws Exception
     */
    public function parse(string $inputPath, string $outputPath): void
    {
        $content = file_get_contents($inputPath);
        if ($content === false) {
            throw new RuntimeException("Could not open input file: $inputPath");
        }

        $lines = explode("\n", $content);
        unset($content);
        if (end($lines) === "") {
            array_pop($lines);
        }

        $visits = [];

        foreach ($lines as $line) {
            $commaPos = strpos($line, ',');
            $pathStart = strpos($line, '/', 8);

            if ($pathStart !== false && $pathStart < $commaPos) {
                $path = substr($line, $pathStart, $commaPos - $pathStart);
            } else {
                $path = '/';
            }

            $date = substr($line, $commaPos + 1, 10);

            if (isset($visits[$path][$date])) {
                $visits[$path][$date]++;
            } else {
                $visits[$path][$date] = 1;
            }
        }

        foreach ($visits as &$dates) {
            ksort($dates);
        }

        file_put_contents(
            $outputPath,
            json_encode($visits, JSON_PRETTY_PRINT)
        );
    }
}