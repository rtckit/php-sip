<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Header\FromHeader;
use RTCKit\SIP\Header\NameAddrHeader;

use PHPUnit\Framework\TestCase;

class FromHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $from = FromHeader::parse([
            'Bob <sips:bob@biloxi.example.com>;tag=4294967295;custom=parameter',
        ]);

        $this->assertNotNull($from);
        $this->assertInstanceOf(NameAddrHeader::class, $from);
        $this->assertEquals('Bob', $from->name);
        $this->assertEquals('sips:bob@biloxi.example.com', $from->uri->render());
        $this->assertEquals('4294967295', $from->tag);
        $this->assertEquals('parameter', $from->params['custom']);
    }

    public function testShouldNotParseMissingTagParameter()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        FromHeader::parse(['Bob <sips:bob@biloxi.example.com>']);
    }
}
