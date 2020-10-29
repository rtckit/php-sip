<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.3
 * 3.1.1.3.  Valid Use of the % Escaping Mechanism
 */
class S31103Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('esc01');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* The request-URI has sips:user@example.com embedded in its
           userpart.  What that might mean to example.net is beyond the scope
           of this document. */
        $this->assertEquals('sip:sips%3Auser%40example.com@example.net', $msg->uri);

        /* The From and To URIs have escaped characters in their userparts. */
        $this->assertEquals('sip:I%20have%20spaces@example.net', $msg->from->addr);
        $this->assertEquals('sip:%75se%72@example.com', $msg->to->addr);

        /* The Contact URI has escaped characters in the URI parameters.
           Note that the "name" uri-parameter has a value of "value%41",
           which is NOT equivalent to "valueA". */
        $this->assertEquals(
            'sip:cal%6Cer@host5.example.net;%6C%72;n%61me=v%61lue%25%34%31',
            $msg->contact->values[0]->addr
        );
    }
}
