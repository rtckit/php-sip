<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Examples;

error_reporting(-1);

require(__DIR__ . '/../vendor/autoload.php');

use RTCKit\SIP\Header\CallIdHeader;
use RTCKit\SIP\Header\ContactHeader;
use RTCKit\SIP\Header\ContactValue;
use RTCKit\SIP\Header\CSeqHeader;
use RTCKit\SIP\Header\Header;
use RTCKit\SIP\Header\NameAddrHeader;
use RTCKit\SIP\Header\ScalarHeader;
use RTCKit\SIP\Header\ViaHeader;
use RTCKit\SIP\Header\ViaValue;
use RTCKit\SIP\Request;

$request = new Request;
$request->version = 'SIP/2.0';
$request->method = 'REGISTER';
$request->uri = 'sip:192.168.0.1';

$request->via = new ViaHeader;
$request->via->values[0] = new ViaValue;
$request->via->values[0]->protocol = 'SIP';
$request->via->values[0]->version = '2.0';
$request->via->values[0]->transport = 'UDP';
$request->via->values[0]->host = '192.168.0.2:5050';
$request->via->values[0]->branch = 'z9hG4bK.eAV4o0nXr';

$request->from = new NameAddrHeader;
$request->from->addr = 'sip:buzz@192.168.0.1';
$request->from->tag = 'SFJbQ2oWh';

$request->to = new NameAddrHeader;
$request->to->addr = 'sip:buzz@192.168.0.1';

$request->cSeq = new CSeqHeader;
$request->cSeq->sequence = 20;
$request->cSeq->method = $request->method;

$request->callId = new CallIdHeader;
$request->callId->value = 'ob0EYyuyC0';

$request->maxForwards = new ScalarHeader;
$request->maxForwards->value = 70;

$request->contact = new ContactHeader;
$request->contact->values[0] = new ContactValue;
$request->contact->values[0]->addr = 'sip:buzz@192.168.0.2:5050;transport=udp';
$request->contact->values[0]->q = 0.7;
$request->contact->values[0]->expires = 3600;
$request->contact->values[0]->params['+sip.instance'] = '"<urn:uuid:5cc54b96-ab90-4652-b4e5-de74c8e56fb7>"';

$request->userAgent = new Header;
$request->userAgent->values[0] = 'MyDeskPhone/1.0.0';

echo $request->render();
