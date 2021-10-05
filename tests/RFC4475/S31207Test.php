<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Exception\InvalidRequestURIException;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.7
 * 3.1.2.7.  <> Enclosing Request-URI
 */
class S31207Test extends RFC4475Case
{
    public function testShouldNotParse()
    {
        $pdu = $this->loadFixture('ltgtruri');

        // Throws InvalidRequestURIException for escaped request URIs
        $this->expectException(InvalidRequestURIException::class);
        Message::parse($pdu);
    }
}
