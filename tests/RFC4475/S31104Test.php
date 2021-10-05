<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;
use RTCKit\SIP\URI;

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
        $this->assertEquals(URI::unescape('null-%00-null'), $msg->to->uri->user);
        $this->assertEquals(0x00, ord($msg->to->uri->user[5]));
        $this->assertEquals(URI::escape(URI::unescape('null-%00-null')), 'null-%00-null');
        $this->assertEquals(URI::unescape('null-%00-null'), $msg->from->uri->user);
        $this->assertEquals(0x00, ord($msg->from->uri->user[5]));
        $this->assertEquals('839923423', $msg->from->tag);
        $this->assertEquals(chr(0x00), $msg->contact->values[0]->uri->user);
        $this->assertEquals(1, strlen($msg->contact->values[0]->uri->user));
        $this->assertEquals(chr(0x00) . chr(0x00), $msg->contact->values[1]->uri->user);
        $this->assertEquals(2, strlen($msg->contact->values[1]->uri->user));
    }
}
