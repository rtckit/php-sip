<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Header\CallIdHeader;

use PHPUnit\Framework\TestCase;

class CallIdHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $callId = CallIdHeader::parse(['  65465496  ']);

        $this->assertNotNull($callId);
        $this->assertInstanceOf(CallIdHeader::class, $callId);
        $this->assertEquals('65465496', $callId->value);
    }

    public function testShouldNotParseMultiValue()
    {
        $this->expectException(InvalidDuplicateHeaderException::class);
        CallIdHeader::parse([
            '78',
            '99',
        ]);
    }

    public function testShouldRenderWellFormedValue()
    {
        $callId = new CallIdHeader;
        $callId->value = '42';

        $rendered = $callId->render('Call-ID');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("Call-ID: 42\r\n", $rendered);
    }

    public function testShouldNotRenderMissingValue()
    {
        $callId = new CallIdHeader;

        $this->expectException(InvalidHeaderValueException::class);
        $callId->render('Call-ID');
    }
}
