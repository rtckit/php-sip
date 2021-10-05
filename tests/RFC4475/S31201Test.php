<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.1
 * 3.1.2.1.  Extraneous Header Field Separators
 */
class S31201Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('badinv01');

        // Throws InvalidHeaderParameterException for blank Via and Contact parameters
        $this->expectException(InvalidHeaderParameterException::class);
        Message::parse($pdu);
    }
}
