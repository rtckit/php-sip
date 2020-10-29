<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Header\Header;

use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $header = Header::parse(['Mysterious value']);

        $this->assertNotNull($header);
        $this->assertInstanceOf(Header::class, $header);
        $this->assertCount(1, $header->values);
        $this->assertEquals('Mysterious value', $header->values[0]);
    }

    public function testShouldRenderWellFormedValue()
    {
        $header = new Header;
        $header->values = [
            'Something useful',
            'Something else',
        ];

        $rendered = $header->render('X-Extra');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'X-Extra: Something useful' . "\r\n" .
            'X-Extra: Something else' . "\r\n",
            $rendered
        );
    }
}
