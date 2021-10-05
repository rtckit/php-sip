<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.14
 * 3.3.14.  REGISTER with a URL Escaped Header
 */
class S33140Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('regescrt');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to
           determine this is a REGISTER request with a URI
           parameter (escaped header, not a contact parameter!) */
        $this->assertEquals('REGISTER', $msg->method);
        $this->assertEquals('<sip:sip.example.com>', $msg->contact->values[0]->uri->headers['Route']);
        $this->assertTrue(!isset($msg->contact->values[0]->params['Route']));
    }
}
