<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.3
 * 3.3.3.  Request-URI with Known but Atypical Scheme
 * Not in direct scope
 */
class S33030Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('novelsc');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to work
           with the request URI scheme as it wishes */
        $this->assertEquals('soap.beep://192.0.2.103:3002', $msg->uri);
    }
}
