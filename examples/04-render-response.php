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
use RTCKit\SIP\Response;
use RTCKit\SIP\URI;

$response = new Response;
$response->version = 'SIP/2.0';
$response->code = 200;

$response->via = new ViaHeader;
$response->via->values[0] = new ViaValue;
$response->via->values[0]->protocol = 'SIP';
$response->via->values[0]->version = '2.0';
$response->via->values[0]->transport = 'UDP';
$response->via->values[0]->host = '192.168.0.2:5050';
$response->via->values[0]->branch = 'z9hG4bK.eAV4o0nXr';

$response->from = new NameAddrHeader;
$response->from->uri = new URI;
$response->from->uri->scheme = 'sip';
$response->from->uri->user = 'buzz';
$response->from->uri->host = '192.168.0.1';
$response->from->tag = 'SFJbQ2oWh';

$response->to = new NameAddrHeader;
$response->to->uri = new URI;
$response->to->uri->scheme = 'sip';
$response->to->uri->user = 'buzz';
$response->to->uri->host = '192.168.0.1';
$response->to->tag = '8cQtUyH6N5N9K';

$response->cSeq = new CSeqHeader;
$response->cSeq->sequence = 20;
$response->cSeq->method = 'REGISTER';

$response->callId = new CallIdHeader;
$response->callId->value = 'ob0EYyuyC0';

$response->maxForwards = new ScalarHeader;
$response->maxForwards->value = 70;

$response->contact = new ContactHeader;
$response->contact->values[0] = new ContactValue;
$response->contact->values[0]->uri = new URI;
$response->contact->values[0]->uri->scheme = 'sip';
$response->contact->values[0]->uri->user = 'buzz';
$response->contact->values[0]->uri->host = '192.168.0.2';
$response->contact->values[0]->uri->port = 5050;
$response->contact->values[0]->uri->transport = 'udp';
$response->contact->values[0]->q = 0.7;
$response->contact->values[0]->expires = 3600;
$response->contact->values[0]->params['+sip.instance'] = '"<urn:uuid:5cc54b96-ab90-4652-b4e5-de74c8e56fb7>"';

$response->userAgent = new Header;
$response->userAgent->values[0] = 'MyDeskPhone/1.0.0';

echo $response->render();
