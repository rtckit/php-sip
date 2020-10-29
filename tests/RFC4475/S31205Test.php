<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidScalarValue;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.5
 * 3.1.2.5.  Response Scalar Fields with Overlarge Values
 */
class S31205Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('scalarlg');

        // Throws InvalidScalarValue for aberrant values
        $this->expectException(InvalidScalarValue::class);
        Message::parse($pdu);
    }
}
