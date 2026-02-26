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
        $handle = fopen($inputPath, 'rb');
        if (!$handle) {
            throw new RuntimeException("Could not open input file: $inputPath");
        }

        $visits = [];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode(',', $line);
            if (count($parts) < 2) {
                continue;
            }

            [$url, $timestamp] = $parts;

            $path = parse_url($url, PHP_URL_PATH);
            $date = substr($timestamp, 0, 10); //YYYY-MM-DD

            if (!isset($visits[$path])) {
                $visits[$path] = [];
            }

            if (!isset($visits[$path][$date])) {
                $visits[$path][$date] = 0;
            }

            $visits[$path][$date]++;
        }

        fclose($handle);

        foreach ($visits as $path => $dates) {
            ksort($visits[$path]);
        }

        file_put_contents(
            $outputPath,
            json_encode($visits, JSON_PRETTY_PRINT)
        );
    }
}