<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidHeaderLineException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.6
 * 3.1.2.6.  Unterminated Quoted String in Display Name
 */
class S31206Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('quotbal');

        // Throws InvalidHeaderLineException for unmatched quotes
        $this->expectException(InvalidHeaderLineException::class);
        Message::parse($pdu);
    }
}
