<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Header\MultiValueHeader;

use PHPUnit\Framework\TestCase;

class MultiValueHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $header = MultiValueHeader::parse(['gzip, compressed']);

        $this->assertNotNull($header);
        $this->assertInstanceOf(MultiValueHeader::class, $header);
        $this->assertCount(2, $header->values);
        $this->assertEquals('gzip', $header->values[0]);
        $this->assertEquals('compressed', $header->values[1]);
    }

    public function testShouldRenderWellFormedValue()
    {
        $header = new MultiValueHeader;
        $header->values = [
            'compressed',
            'gzip',
        ];

        $rendered = $header->render('Accept-Encoding');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("Accept-Encoding: compressed, gzip\r\n", $rendered);
    }
}
