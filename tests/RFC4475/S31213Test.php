<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.2.13
 * 3.1.2.13.  Failure to Enclose name-addr URI in <>
 *
 * As an UAS, this implementation will take the liberal route and accept
 * unenclosed SIP name-addr URIs
 */
class S31213Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('regbadct');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);
        $this->assertEquals('<sip:sip.example.com>', $msg->contact->values[0]->uri->headers['Route']);

        /* However, as an UAC, this implementation will always enclose SIP name-addr URIs */
        $this->assertEquals(
            "Contact: <sip:user@example.com?Route=%3Csip%3Asip.example.com%3E>\r\n",
            $msg->contact->render('Contact')
        );
    }
}
