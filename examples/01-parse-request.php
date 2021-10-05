<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Examples;

error_reporting(-1);

require(__DIR__ . '/../vendor/autoload.php');

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/* Plain text message we're going to parse */
$text =
    'REGISTER sip:192.168.0.1 SIP/2.0' . "\r\n" .
    'Via: SIP/2.0/UDP 192.168.0.2:5050;branch=z9hG4bK.eAV4o0nXr;rport' . "\r\n" .
    'From: <sip:buzz@192.168.0.1>;tag=SFJbQ2oWh' . "\r\n" .
    'To: sip:buzz@192.168.0.1' . "\r\n" .
    'CSeq: 20 REGISTER' . "\r\n" .
    'Call-ID: ob0EYyuyC0' . "\r\n" .
    'Max-Forwards: 70' . "\r\n" .
    'Supported: replaces, outbound' . "\r\n" .
    'Accept: application/sdp' . "\r\n" .
    'Accept: text/plain' . "\r\n" .
    'Accept: application/vnd.gsma.rcs-ft-http+xml' . "\r\n" .
    'Contact: <sip:buzz@192.168.0.2:5050;transport=udp>' . "\r\n" .
    ' ;q=0.7; expires=3600' . "\r\n" .
    ' ;+sip.instance="<urn:uuid:5cc54b96-ab90-4652-b4e5-de74c8e56fb7>"' . "\r\n" .
    'Contact: <sip:bob@192.0.2.2;transport=tcp>;reg-id=1;expires=3600' . "\r\n" .
    ' ;+sip.instance="<urn:uuid:00000000-0000-1000-8000-AABBCCDDEEFF>"' . "\r\n" .
    'Contact: <sip:bob@192.0.2.2;transport=tcp>;reg-id=2;expires=3600' . "\r\n" .
    ' ;+sip.instance="<urn:uuid:00000000-0000-1000-8000-AABBCCDDEEFF>"' . "\r\n" .
    'Expires: 3600' . "\r\n" .
    'User-Agent: MyDeskPhone/1.0.0' . "\r\n" .
    "\r\n";

$request = Message::parse($text);

printf("Message type:       %s" . PHP_EOL, (get_class($request) === Request::class) ? 'SIP Request' : 'BOGUS!!!');
printf("Protocol version:   %s" . PHP_EOL, $request->version);
printf("Request method:     %s" . PHP_EOL, $request->method);
printf("Request URI scheme: %s" . PHP_EOL, $request->uri->scheme);
printf("Request URI host:   %s" . PHP_EOL, $request->uri->host);
printf("Via:                %s" . PHP_EOL, $request->via->values[0]->host);
printf("Via branch:         %s" . PHP_EOL, $request->via->values[0]->branch);
printf("From scheme:        %s" . PHP_EOL, $request->from->uri->scheme);
printf("From user:          %s" . PHP_EOL, $request->from->uri->user);
printf("From host:          %s" . PHP_EOL, $request->from->uri->host);
printf("From tag:           %s" . PHP_EOL, $request->from->tag);
printf("To scheme:          %s" . PHP_EOL, $request->to->uri->scheme);
printf("To user:            %s" . PHP_EOL, $request->to->uri->user);
printf("To host:            %s" . PHP_EOL, $request->to->uri->host);
printf("CSeq:               %s" . PHP_EOL, $request->cSeq->sequence);
printf("Call ID:            %s" . PHP_EOL, $request->callId->value);
printf("Max forwards:       %d" . PHP_EOL, $request->maxForwards->value);

printf("Supported:         ");

foreach ($request->supported->values as $key => $val) {
    if ($key) {
        printf("                   ");
    }

    printf("- %s" . PHP_EOL, $val);
}

printf("Accept:            ");

foreach ($request->accept->values as $key => $val) {
    if ($key) {
        printf("                   ");
    }

    printf("- %s" . PHP_EOL, $val->value);
}

printf("Contact scheme:    %s" . PHP_EOL, $request->contact->values[0]->uri->scheme);
printf("Contact user:      %s" . PHP_EOL, $request->contact->values[0]->uri->user);
printf("Contact host:      %s" . PHP_EOL, $request->contact->values[0]->uri->host);
printf("Contact transport: %s" . PHP_EOL, $request->contact->values[0]->uri->transport);
printf("Contact q-value:   %s" . PHP_EOL, $request->contact->values[0]->q);
printf("Contact expires:   %s" . PHP_EOL, $request->contact->values[0]->expires);
printf("Contact instance:  %s" . PHP_EOL, $request->contact->values[0]->params['+sip.instance']);
printf("Expires:           %s" . PHP_EOL, $request->expires->value);
printf("User agent:        %s" . PHP_EOL, $request->userAgent->values[0]);
