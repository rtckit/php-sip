<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidScalarValueException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.4
 * 3.1.2.4.  Request Scalar Fields with Overlarge Values
 */
class S31204Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('scalar02');

        // Throws InvalidScalarValueException for aberrant values
        $this->expectException(InvalidScalarValueException::class);
        Message::parse($pdu);
    }
}
