<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.5
 * 3.1.1.5.  Use of % When It Is Not an Escape
 */
class S31105Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('esc02');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* The request method is unknown.  It is NOT equivalent to REGISTER. */
        $this->assertEquals('RE%47IST%45R', $msg->method);

        /* The display name portion of the To and From header fields is
           "%Z%45".  Note that this is not the same as %ZE. */
        $this->assertEquals('%Z%45', $msg->to->name);
        $this->assertEquals('%Z%45', $msg->from->name);

        /* This message has two Contact header field values, not three.
           <sip:alias2@host2.example.com> is a C%6Fntact header field value. */
        $this->assertCount(2, $msg->contact->values);
        $this->assertEquals('sip:alias1@host1.example.com', $msg->contact->values[0]->uri->render());
        $this->assertEquals('sip:alias3@host3.example.com', $msg->contact->values[1]->uri->render());
        $this->assertEquals('<sip:alias2@host2.example.com>', $msg->extraHeaders['c%6fntact']->values[0]);
    }
}
