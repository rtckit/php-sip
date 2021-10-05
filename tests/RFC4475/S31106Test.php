<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.6
 * 3.1.1.6.  Message with No LWS between Display Name and <
 */
class S31106Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('lwsdisp');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        $this->assertEquals('OPTIONS', $msg->method);

        /* This OPTIONS request is not valid per the grammar in RFC 3261 since
           there is no LWS between the token in the display name and < in the
           From header field value.  This has been identified as a specification
           bug that will be removed when RFC 3261 is revised.  Elements should
           accept this request as well formed. */
        $this->assertEquals('sip', $msg->from->uri->scheme);
        $this->assertEquals('caller', $msg->from->uri->user);
        $this->assertEquals('example.com', $msg->from->uri->host);
        $this->assertEquals('caller', $msg->from->name);
        $this->assertEquals('323', $msg->from->tag);
    }
}
