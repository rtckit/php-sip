<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Examples;

error_reporting(-1);

require(__DIR__ . '/../vendor/autoload.php');

use RTCKit\SIP\Message;
use RTCKit\SIP\Response;

/* Plain text message we're going to parse */
$text =
    'SIP/2.0 200 OK' . "\r\n" .
    'Via: SIP/2.0/UDP 198.162.0.2:5060;branch=z9hG4bK4a88.af94d704000000000000000000000000.0' . "\r\n" .
    'From: <sip:probe@192.168.0.2>;tag=8734a29d0ba1b4b0a195a091c7233452-6e53' . "\r\n" .
    'To: <sip:198.162.0.1:5080>;tag=8cQtUyH6N5N9K' . "\r\n" .
    'Call-ID: 5a3202932ab4128f-23935' . "\r\n" .
    'User-Agent: MyDeskPhone/1.0.0' . "\r\n" .
    'CSeq: 10 OPTIONS' . "\r\n" .
    'Contact: <sip:gw@198.162.0.1:5080;transport=udp>' . "\r\n" .
    'Accept: application/sdp' . "\r\n" .
    'Allow: INVITE, ACK, BYE, CANCEL, OPTIONS, MESSAGE, INFO, UPDATE, REGISTER, REFER, NOTIFY' . "\r\n" .
    'Supported: timer' . "\r\n" .
    'Supported: path' . "\r\n" .
    'Supported: replaces' . "\r\n" .
    'Allow-Events: talk' . "\r\n" .
    'Content-Length: 0' . "\r\n" .
    "\r\n";

$response = Message::parse($text);

printf("Message type:      %s" . PHP_EOL, (get_class($response) === Response::class) ? 'SIP Response' : 'BOGUS!!!');
printf("Protocol version:  %s" . PHP_EOL, $response->version);
printf("Response code:     %s" . PHP_EOL, $response->code);
printf("Response reason:   %s" . PHP_EOL, $response->reason);
printf("Via:               %s" . PHP_EOL, $response->via->values[0]->host);
printf("Via branch:        %s" . PHP_EOL, $response->via->values[0]->branch);
printf("From scheme:       %s" . PHP_EOL, $response->from->uri->scheme);
printf("From user:         %s" . PHP_EOL, $response->from->uri->user);
printf("From host:         %s" . PHP_EOL, $response->from->uri->host);
printf("From tag:          %s" . PHP_EOL, $response->from->tag);
printf("To scheme:         %s" . PHP_EOL, $response->to->uri->scheme);
printf("To host:           %s" . PHP_EOL, $response->to->uri->host);
printf("To tag:            %s" . PHP_EOL, $response->to->tag);
printf("Call ID:           %s" . PHP_EOL, $response->callId->value);
printf("User agent:        %s" . PHP_EOL, $response->userAgent->values[0]);
printf("CSeq:              %s" . PHP_EOL, $response->cSeq->sequence);
printf("Contact scheme:    %s" . PHP_EOL, $response->contact->values[0]->uri->scheme);
printf("Contact user:      %s" . PHP_EOL, $response->contact->values[0]->uri->user);
printf("Contact host:      %s" . PHP_EOL, $response->contact->values[0]->uri->host);
printf("Contact port:      %s" . PHP_EOL, $response->contact->values[0]->uri->port);
printf("Contact transport: %s" . PHP_EOL, $response->contact->values[0]->uri->transport);

printf("Accept:            ");

foreach ($response->accept->values as $key => $val) {
    if ($key) {
        printf("                   ");
    }

    printf("- %s" . PHP_EOL, $val->value);
}

printf("Allow:             ");

foreach ($response->allow->values as $key => $val) {
    if ($key) {
        printf("                   ");
    }

    printf("- %s" . PHP_EOL, $val);
}

printf("Supported:         ");

foreach ($response->supported->values as $key => $val) {
    if ($key) {
        printf("                   ");
    }

    printf("- %s" . PHP_EOL, $val);
}

printf("Allow events:      ");

foreach ($response->allowEvents->values as $key => $val) {
    if ($key) {
        printf("                   ");
    }

    printf("- %s" . PHP_EOL, $val);
}

printf("Content length:    %s" . PHP_EOL, $response->contentLength->value);
