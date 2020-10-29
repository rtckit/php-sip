<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.15
 * 3.3.15.  Unacceptable Accept Offering
 * Not in direct scope
 */
class S33150Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('sdp01');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to
           read the Accept reader */
        $this->assertEquals('text/nobodyKnowsThis', $msg->accept->values[0]->value);
    }
}
