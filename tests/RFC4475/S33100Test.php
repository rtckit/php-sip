<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Response;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.10
 * 3.3.10.  200 OK Response with Broadcast Via Header Field Value
 * Not in direct scope
 */
class S33100Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('bcast');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Response::class, $msg);

        /* Make sure the application has enough visibility to read the broadcast address */
        $this->assertEquals('255.255.255.255', $msg->via->values[1]->host);
    }
}
