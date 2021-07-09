<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Examples;

error_reporting(-1);

require(__DIR__ . '/../vendor/autoload.php');

use RTCKit\SIP\Message;
use RTCKit\SIP\StreamParser;

use Symfony\Component\Yaml\Yaml;

$flows = [];

$fdir = __DIR__ . '/../tests/fixtures/misc';
$dirs = scandir($fdir);

foreach ($dirs as $dir) {
    $dir = $fdir . '/' . $dir;

    if (!is_dir($dir) || ($dir[0] === '.')) {
        continue;
    }

    $files = scandir($dir);

    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) != 'yaml') {
            continue;
        }

        $flows[$file] = Yaml::parse(file_get_contents($dir . '/' . $file));
        $flows[$file] = preg_replace('~\R~u', "\r\n", $flows[$file]);
    }
}

$exp = extension_loaded('xdebug') ? 8 : 10;
$runs = 0;
$stats = [];
$elapsed = 0;

for ($i = 0; $i < $exp; $i++) {
    $bound = pow(2, $i);

    for ($k = 0; $k < $bound; $k++) {
        foreach ($flows as $file => $flow) {
            foreach ($flow as $j => $message) {
                $id = sprintf("%s[%02d]", $file, $j);

                if (!isset($stats[$id])) {
                    $stats[$id] = [];
                }

                $start = hrtime(true);
                $m = Message::parse($message);

                $delta = hrtime(true) - $start;

                $elapsed += $delta;
                $stats[$id][] = $delta;

                $runs++;
            }
        }
    }

    printf(
        "Running, parsed %d messages = %.03f (%.06f/message) (%d/second)" . PHP_EOL,
        $runs,
        $elapsed / 1e9,
        ($elapsed / 1e9) / $runs,
        $runs / ($elapsed / 1e9)
    );
}

$elapsed = $elapsed / 1e9;
$perReq = $elapsed / $runs;
$perSec = $runs / $elapsed;

printf("====[PDU Parsing]======================================================================================" . PHP_EOL);
printf("%d runs = %.03f (%.06f/message) (%d/second)" . PHP_EOL, $runs, $elapsed, $perReq, $perSec);
printf("=======================================================================================================" . PHP_EOL);
printf("File/Message                                                           | Min      | Max      | Avg     " . PHP_EOL);
printf("-------------------------------------------------------------------------------------------------------" . PHP_EOL);

foreach ($stats as $id => $results) {
    printf(
        "%70s | %.06f | %.06f | %.06f" . PHP_EOL,
        $id,
        min($results) / 1e9,
        max($results) / 1e9,
        (array_sum($results) / count($results)) / 1e9
    );
}

printf("=======================================================================================================" . PHP_EOL);
printf("%d runs = %.03f (%.06f/message) (%d/second)" . PHP_EOL, $runs, $elapsed, $perReq, $perSec);
printf("====[/PDU Parsing]=====================================================================================" . PHP_EOL . PHP_EOL);

if (extension_loaded('xdebug')) {
    exit;
}

$parser = new StreamParser;
$chunkExp = 17;
$runs = 0;
$stats = [];
$elapsed = 0;
$fp = fopen(__DIR__ . '/../tests/fixtures/stream/generic.txt', 'r');

for ($i = 6; $i < $chunkExp; $i++) {
    $chunkSize = pow(2, $i);
    $bound = 100;

    for ($k = 0; $k < $bound; $k++) {
        $id = sprintf("%04d_chunks", $chunkSize);

        if (!isset($stats[$id])) {
            $stats[$id] = [];
        }

        fseek($fp, 0);

        while (!feof($fp)) {
            $bytes = fread($fp, $chunkSize);

            $start = hrtime(true);
            $ret = $parser->process($bytes, $messages);
            $_delta = hrtime(true) - $start;

            if (isset($delta)) {
                $delta += $_delta;
            } else {
                $delta = $_delta;
            }

            if ($ret === StreamParser::SUCCESS) {
                $runs += count($messages);
                $stats[$id][] = $delta / count($messages);
                unset($delta);
            }

            $elapsed += $_delta;
        }
    }

    printf(
        "Running, %d byte chunks, parsed %d messages = %.03f (%.06f/message) (%d/second)" . PHP_EOL,
        $chunkSize,
        $runs,
        $elapsed / 1e9,
        ($elapsed / 1e9) / $runs,
        $runs / ($elapsed / 1e9)
    );
}

$elapsed = $elapsed / 1e9;
$perReq = $elapsed / $runs;
$perSec = $runs / $elapsed;

fclose($fp);

printf("====[Stream Parsing]====================================" . PHP_EOL);
printf("%d runs = %.03f (%.06f/message) (%d/second)" . PHP_EOL, $runs, $elapsed, $perReq, $perSec);
printf("========================================================" . PHP_EOL);
printf("Chunk size              | Min      | Max      | Avg     " . PHP_EOL);
printf("--------------------------------------------------------" . PHP_EOL);

foreach ($stats as $id => $results) {
    printf(
        "%23s | %.06f | %.06f | %.06f" . PHP_EOL,
        $id,
        min($results) / 1e9,
        max($results) / 1e9,
        (array_sum($results) / count($results)) / 1e9
    );
}

printf("========================================================" . PHP_EOL);
printf("%d runs = %.03f (%.06f/message) (%d/second)" . PHP_EOL, $runs, $elapsed, $perReq, $perSec);
printf("====[/Stream Parsing]===================================" . PHP_EOL . PHP_EOL);

if (function_exists('opcache_get_status')) {
    $output = opcache_get_status(false);

    if (empty($output)) {
        exit;
    }

    printf("====[opcache]================================================" . PHP_EOL);

    foreach ($output as $category => $values) {
        if (is_scalar($values)) {
            printf("%40s = %s" . PHP_EOL, $category, $values);

            continue;
        }

        foreach ($values as $k => $v) {
            printf("%40s = %s" . PHP_EOL, $category . '.' . $k, $v);
        }
    }

    printf("====[/opcache]===============================================" . PHP_EOL . PHP_EOL);
}
