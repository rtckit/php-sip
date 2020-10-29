<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.4
 * 3.1.1.4.  Escaped Nulls in URIs
 */
class S31104Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('escnull');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* This register request contains several URIs with nulls in the
           userpart.  The message is well formed - parsers must accept this
           message.  Implementations must take special care when unescaping the
           Address-of-Record (AOR) in this request so as to not prematurely
           shorten the username.  This request registers two distinct contact
           URIs. */
        $this->assertEquals('sip:null-%00-null@example.com', $msg->to->addr);
        $this->assertEquals('sip:null-%00-null@example.com', $msg->from->addr);
        $this->assertEquals('839923423', $msg->from->tag);
        $this->assertEquals('sip:%00@host5.example.com', $msg->contact->values[0]->addr);
        $this->assertEquals('sip:%00%00@host5.example.com', $msg->contact->values[1]->addr);
    }
}
