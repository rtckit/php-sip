<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.3.5
 * 3.3.5.  Proxy-Require and Require
 * Not in direct scope
 */
class S33050Test extends RFC4475Case
{
    public function testShouldParse()
    {
        $pdu = $this->loadFixture('bext01');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* Make sure the application has enough visibility to read *Require header fields */
        $this->assertEquals('nothingSupportsThis', $msg->require->values[0]);
        $this->assertEquals('nothingSupportsThisEither', $msg->require->values[1]);
        $this->assertEquals('noProxiesSupportThis', $msg->proxyRequire->values[0]);
        $this->assertEquals('norDoAnyProxiesSupportThis', $msg->proxyRequire->values[1]);
    }
}
