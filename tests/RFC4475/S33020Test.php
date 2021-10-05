<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.2
 * 3.3.2.  Request-URI with Unknown Scheme
 */
class S33020Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('unkscm');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to work
           with the request URI scheme as it wishes */
        $this->assertEquals('nobodyknowsthisscheme', $msg->uri->scheme);
        $this->assertEquals('totallyopaquecontent', $msg->uri->host);
    }
}
