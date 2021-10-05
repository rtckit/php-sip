<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.4
 * 3.3.4.  Unknown URI Schemes in Header Fields
 */
class S33040Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('unksm2');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to work
           with the To header field value scheme as it wishes */
        $this->assertEquals('isbn', $msg->to->uri->scheme);
        $this->assertEquals('2983792873', $msg->to->uri->host);
    }
}
