<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.1
 * 3.1.1.1.  A Short Tortuous INVITE
 */
class S31101Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('wsinv');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* escaped characters within quotes */
        $this->assertEquals("J Rosenberg \\\\\"", $msg->from->name);
        $this->assertEquals('Quoted string \\"\\"', $msg->contact->values[0]->name);

        /* an empty subject */
        $this->assertEquals('', $msg->subject->values[0]);

        /* both comma separated and separately listed header field values */
        /* a mix of short and long form for the same header field name */
        $this->assertCount(3, $msg->via->values);

        // 2D! unknown Request-URI parameter

        /* unknown header fields */
        $this->assertNotNull($msg->extraHeaders['newfangledheader']->values[0]);
        $this->assertNotNull($msg->extraHeaders['unknownheaderwithunusualvalue']->values[0]);

        /* an unknown header field with a value that would be syntactically invalid if it were defined in terms of generic-param */
        $this->assertEquals(';;,,;;,;', $msg->extraHeaders['unknownheaderwithunusualvalue']->values[0]);

        /* unknown parameters of a known header field */
        $this->assertEquals('newvalue', $msg->contact->values[0]->params['newparam']);

        /* a header parameter with no value */
        $this->assertEquals('', $msg->contact->values[0]->params['secondparam']);

        // 2D! a uri parameter with no value

        /* integer fields (Max-Forwards and CSeq) with leading zeros */
        $this->assertIsNumeric($msg->maxForwards->value);
        $this->assertEquals(68, $msg->maxForwards->value);
        $this->assertIsNumeric($msg->cSeq->sequence);
        $this->assertEquals(9, $msg->cSeq->sequence);
        $this->assertIsString($msg->cSeq->method);
        $this->assertEquals('INVITE', $msg->cSeq->method);
    }
}
