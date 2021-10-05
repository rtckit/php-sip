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
        $this->assertEquals('sip', $msg->uri->scheme);
        $this->assertEquals('sips:user@example.com', $msg->uri->user);
        $this->assertEquals('example.net', $msg->uri->host);
        $this->assertFalse(isset($msg->uri->password));

        /* The From and To URIs have escaped characters in their userparts. */
        $this->assertEquals('sip', $msg->from->uri->scheme);
        $this->assertEquals('I have spaces', $msg->from->uri->user);
        $this->assertEquals('example.net', $msg->from->uri->host);
        $this->assertFalse(isset($msg->from->uri->password));

        $this->assertEquals('sip', $msg->to->uri->scheme);
        $this->assertEquals('user', $msg->to->uri->user);
        $this->assertEquals('example.com', $msg->to->uri->host);
        $this->assertFalse(isset($msg->to->uri->password));

        /* The Contact URI has escaped characters in the URI parameters.
           Note that the "name" uri-parameter has a value of "value%41",
           which is NOT equivalent to "valueA". */
        $this->assertEquals('sip', $msg->contact->values[0]->uri->scheme);
        $this->assertEquals('caller', $msg->contact->values[0]->uri->user);
        $this->assertEquals('host5.example.net', $msg->contact->values[0]->uri->host);
        $this->assertFalse(isset($msg->contact->values[0]->uri->password));
        $this->assertEquals('', $msg->contact->values[0]->uri->lr);
        $this->assertEquals('value%41', $msg->contact->values[0]->uri->params['name']);
        $this->assertNotEquals('valueA', $msg->contact->values[0]->uri->params['name']);
    }
}
