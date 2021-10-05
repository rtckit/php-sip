<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Examples;

error_reporting(-1);

require(__DIR__ . '/../vendor/autoload.php');

use RTCKit\SIP\Request;
use RTCKit\SIP\Response;
use RTCKit\SIP\StreamParser;

$parser = new StreamParser;

$fp = fopen(__DIR__ . '/../tests/fixtures/stream/generic.txt', 'r');

while (!feof($fp)) {
    $bytes = fread($fp, 256);

    if ($parser->process($bytes, $messages) === StreamParser::SUCCESS) {
        foreach($messages as $message) {
            /* Consume messages ... */
            if ($message instanceof Request) {
                printf(
                    "Request  %10s %30s %30s %40s" . PHP_EOL,
                    $message->method,
                    substr($message->from->uri->user, 0, 30),
                    substr($message->to->uri->user, 0, 30),
                    substr($message->callId->value, 0, 40)
                );
            } else {
                printf(
                    "Response %10s %30s %30s %40s %03d %s" . PHP_EOL,
                    $message->cSeq->method,
                    substr($message->from->uri->user, 0, 30),
                    substr($message->to->uri->user, 0, 30),
                    substr($message->callId->value, 0, 40),
                    $message->code,
                    $message->reason
                );
            }
        }
    }
}
