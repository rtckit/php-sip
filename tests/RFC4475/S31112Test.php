<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Response;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.12
 * 3.1.1.12.  Unusual Reason Phrase
 */
class S31112Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('unreason');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Response::class, $msg);

        /* This particular response contains unreserved and non-ascii UTF-8
           characters.  This response is well formed.  A parser must accept this
           message */
        $this->assertEquals(
            '= 2**3 * 5**2 ' .
            "\xD0\xBD\xD0\xBE\x20\xD1\x81\xD1\x82" .
            "\xD0\xBE\x20\xD0\xB4\xD0\xB5\xD0\xB2\xD1\x8F\xD0\xBD\xD0\xBE\xD1\x81\xD1\x82\xD0\xBE\x20\xD0\xB4" .
            "\xD0\xB5\xD0\xB2\xD1\x8F\xD1\x82\xD1\x8C\x20\x2D\x20\xD0\xBF\xD1\x80\xD0\xBE\xD1\x81\xD1\x82\xD0" .
            "\xBE\xD0\xB5",
            $msg->reason
        );
    }
}
