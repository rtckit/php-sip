<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.10
 * 3.1.1.10.  Varied and Unknown Transport Types
 */
class S31110Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('transports');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* This request contains Via header field values with all known
           transport types and exercises the transport extension mechanism.
           Parsers must accept this message as well formed.  Elements receiving
           this message would process it exactly as if the 2nd and subsequent
           header field values specified UDP (or other transport). */
        $this->assertEquals('UDP', $msg->via->values[0]->transport);
        $this->assertEquals('SCTP', $msg->via->values[1]->transport);
        $this->assertEquals('TLS', $msg->via->values[2]->transport);
        $this->assertEquals('UNKNOWN', $msg->via->values[3]->transport);
        $this->assertEquals('TCP', $msg->via->values[4]->transport);
    }
}
