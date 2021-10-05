<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.12
 * 3.1.2.12.  Invalid Time Zone in Date Header Field
 */
class S31212Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('baddate');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        $this->assertEquals('Fri, 01 Jan 2010 16:00:00 EST', $msg->date->values[0]);
    }
}
