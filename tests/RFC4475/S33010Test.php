<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.1
 * 3.3.1.  Missing Required Header Fields
 * Not in direct scope
 */
class S33010Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('insuf');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to determine
           Call-ID, From, or To headers are missing */
        $this->assertFalse(isset($msg->callId));
        $this->assertFalse(isset($msg->from));
        $this->assertFalse(isset($msg->to));
    }
}
