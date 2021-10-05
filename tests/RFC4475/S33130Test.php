<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.13
 * 3.3.13.  REGISTER with a url-parameter
 */
class S33130Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('cparam02');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to
           determine this is a REGISTER request with a URI
           parameter (not a contact parameter!) */
        $this->assertEquals('REGISTER', $msg->method);
        $this->assertEquals('sip', $msg->contact->values[0]->uri->scheme);
        $this->assertEquals('+19725552222', $msg->contact->values[0]->uri->user);
        $this->assertEquals('gw1.example.net', $msg->contact->values[0]->uri->host);
        $this->assertEquals('', $msg->contact->values[0]->uri->params['unknownparam']);
        $this->assertTrue(!isset($msg->contact->values[0]->params['unknownparam']));
    }
}
