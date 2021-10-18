<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.7
 * 3.3.7.  Unknown Authorization Scheme
 * Not in direct scope
 */
class S33070Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('regaut01');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility read the authorization */
        $this->assertEquals('nooneknowsthisscheme', $msg->authorization->values[0]->scheme);
        $this->assertEquals('opaque-data=here', $msg->authorization->values[0]->params->verbatim);
    }
}
