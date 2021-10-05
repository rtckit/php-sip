<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.7
 * 3.1.1.7.  Long Values in Header Fields
 */
class S31107Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('longreq');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* The To header field has a long display name, and long uri parameter names and values. */
        $this->assertEquals('sip', $msg->to->uri->scheme);
        $this->assertEquals('user', $msg->to->uri->user);
        $this->assertEquals('example.com', $msg->to->uri->host);
        $this->assertEquals(6000, $msg->to->uri->port);
        $this->assertEquals('very' . str_repeat('long', 20) . 'value', $msg->to->uri->params['unknownparam1']);
        $this->assertEquals('shortvalue', $msg->to->uri->params['longparam' . str_repeat('name', 25)]);
        $this->assertEquals('', $msg->to->uri->params['very' . str_repeat('long', 25) . 'parameternamewithnovalue']);
        $this->assertEquals('I have a user name of ' . str_repeat('extreme', 10) . ' proportion', $msg->to->name);

        /* The From header field has long header parameter names and values, in particular, a very long tag. */
        $this->assertEquals('sip', $msg->from->uri->scheme);
        $this->assertEquals(str_repeat('amazinglylongcallername', 5), $msg->from->uri->user);
        $this->assertEquals('example.net', $msg->from->uri->host);
        $this->assertEquals('12' . str_repeat('982', 50) . '424', $msg->from->tag);
        $this->assertEquals(
            'unknowheaderparam' . str_repeat('value', 15),
            $msg->from->params['unknownheaderparam' . str_repeat('name', 20)]
        );
        $this->assertEquals('', $msg->from->params['unknownValueless' . str_repeat('paramname', 10)]);

        /* The Call-ID is one long token */
        $this->assertEquals('longreq.one' . str_repeat('really', 20) . 'longcallid', $msg->callId->value);

        /* Other cases */
        $this->assertEquals(
            'unknown-' .
            str_repeat('long', 20) .
            '-value; unknown-' .
            str_repeat('long', 20) .
            '-parameter-name = unknown-' .
            str_repeat('long', 20) .
            '-parameter-value',
            $msg->extraHeaders['unknown-' . str_repeat('long', 20) . '-name']->values[0]
        );
        $this->assertCount(34, $msg->via->values);
        $this->assertEquals(
            'very' . str_repeat('long', 50) . 'branchvalue',
            $msg->via->values[33]->branch
        );
        $this->assertEquals(str_repeat('amazinglylongcallername', 5), $msg->contact->values[0]->uri->user);
    }
}
