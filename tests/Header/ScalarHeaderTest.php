<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidDuplicateHeader;
use RTCKit\SIP\Exception\InvalidScalarValue;
use RTCKit\SIP\Exception\InvalidHeaderValue;
use RTCKit\SIP\Header\ScalarHeader;

use PHPUnit\Framework\TestCase;

class ScalarHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $scalar = ScalarHeader::parse(['  65465496  ']);

        $this->assertNotNull($scalar);
        $this->assertInstanceOf(ScalarHeader::class, $scalar);
        $this->assertEquals(65465496, $scalar->value);
    }

    public function testShouldNotParseMultiValue()
    {
        $this->expectException(InvalidDuplicateHeader::class);
        ScalarHeader::parse([
            '78',
            '99',
        ]);
    }

    public function testShouldNotParseNegativeValue()
    {
        $this->expectException(InvalidScalarValue::class);
        ScalarHeader::parse(['-1969']);
    }

    public function testShouldNotParseOutOfBoundsValue()
    {
        $this->expectException(InvalidScalarValue::class);
        ScalarHeader::parse(['42949672950']);
    }

    public function testShouldRenderWellFormedValue()
    {
        $scalar = new ScalarHeader;
        $scalar->value = 42;

        $rendered = $scalar->render('Max-Forwards');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("Max-Forwards: 42\r\n", $rendered);
    }

    public function testShouldNotRenderMissingValue()
    {
        $scalar = new ScalarHeader;

        $this->expectException(InvalidHeaderValue::class);
        $scalar->render('Max-Forwards');
    }
}
