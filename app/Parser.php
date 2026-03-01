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
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }

            $commaPos = strpos($line, ',');
            if ($commaPos === false) {
                continue;
            }

            // Extract path directly from line starting from after ://
            $pathStart = strpos($line, '/', 8); 
            $path = ($pathStart !== false && $pathStart < $commaPos) 
                ? substr($line, $pathStart, $commaPos - $pathStart) 
                : '/';

            $date = substr($line, $commaPos + 1, 10); //YYYY-MM-DD

            if (!isset($visits[$path])) {
                $visits[$path] = [$date => 1];
            } elseif (!isset($visits[$path][$date])) {
                $visits[$path][$date] = 1;
            } else {
                $visits[$path][$date]++;
            }
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