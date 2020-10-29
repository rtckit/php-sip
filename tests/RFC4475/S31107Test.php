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
        $this->assertEquals(
            'sip:user@example.com:6000;unknownparam1=very' .
            str_repeat('long', 20) .
            'value;longparam' .
            str_repeat('name', 25) .
            '=shortvalue;very' .
            str_repeat('long', 25) .
            'ParameterNameWithNoValue',
            $msg->to->addr
        );
        $this->assertEquals(
            'I have a user name of ' . str_repeat('extreme', 10) . ' proportion',
            $msg->to->name
        );

        /* The From header field has long header parameter names and values, in particular, a very long tag. */
        $this->assertEquals(
            'sip:' . str_repeat('amazinglylongcallername', 5) . '@example.net',
            $msg->from->addr
        );
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
        $this->assertEquals(
            'sip:' . str_repeat('amazinglylongcallername', 5) . '@host5.example.net',
            $msg->contact->values[0]->addr
        );
    }
}
