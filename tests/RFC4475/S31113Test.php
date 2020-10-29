<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Response;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.13
 * 3.1.1.13.  Empty Reason Phrase
 */
class S31113Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('noreason');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Response::class, $msg);

        /* This well-formed response contains no reason phrase.  A parser must
           accept this message.  The space character after the reason code is
           required.  If it were not present, this message could be rejected as
           invalid (a liberal receiver would accept it anyway). */
        $this->assertEquals('', $msg->reason);
    }
}
