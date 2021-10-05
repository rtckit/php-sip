<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\URI;
use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Header\NameAddrHeader;

use PHPUnit\Framework\TestCase;

class NameAddrHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $header = NameAddrHeader::parse(['"Bob \" Quote" <sips:bob@biloxi.example.com>;custom=parameter']);

        $this->assertNotNull($header);
        $this->assertInstanceOf(NameAddrHeader::class, $header);
        $this->assertEquals('Bob \" Quote', $header->name);
        $this->assertEquals('sips:bob@biloxi.example.com', $header->uri->render());
        $this->assertEquals('parameter', $header->params['custom']);
    }

    public function testShouldParseUnquotedNameValue()
    {
        $header = NameAddrHeader::parse(['Bob <sips:bob@biloxi.example.com>']);

        $this->assertNotNull($header);
        $this->assertInstanceOf(NameAddrHeader::class, $header);
        $this->assertEquals('Bob', $header->name);
        $this->assertEquals('sips:bob@biloxi.example.com', $header->uri->render());
    }

    public function testShouldParseWithoutEscapedURIValue()
    {
        $header = NameAddrHeader::parse(['sips:bob@biloxi.example.com']);

        $this->assertNotNull($header);
        $this->assertInstanceOf(NameAddrHeader::class, $header);
        $this->assertEquals('sips:bob@biloxi.example.com', $header->uri->render());
    }

    public function testShouldNotParseEmptyValues()
    {
        $this->expectException(InvalidHeaderLineException::class);
        NameAddrHeader::parse(['']);
    }

    public function testShouldNotParseMultipleValues()
    {
        $this->expectException(InvalidDuplicateHeaderException::class);
        NameAddrHeader::parse([
            '<sips:bob@offshore.biloxi.example.com',
            '<sip:alice@biloxi.example.com',
        ]);
    }

    public function testShouldNotParseBeginingEndingEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        NameAddrHeader::parse(['<sips:bob@offshore.biloxi.example.com']);
    }

    public function testShouldNotParseUnmatchedMiddleEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        NameAddrHeader::parse(['<sips:<bob@offshore.biloxi.example.com>']);
    }

    public function testShouldNotParseUnmatchedEndingEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        NameAddrHeader::parse(['sips:bob@offshore.biloxi.example.com>']);
    }

    public function testShouldNotParseUnmatchedParameterEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        NameAddrHeader::parse(['<sips:bob@offshore.biloxi.example.com>;>custom=param']);
    }

    public function testShouldNotParseEmptyParameterNames()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        NameAddrHeader::parse(['sips:bob@offshore.biloxi.example.com;tag=sa42d23;;custom=param']);
    }

    public function testShouldNotParseEmptyParameterNames2()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        NameAddrHeader::parse(['sips:bob@offshore.biloxi.example.com; =param']);
    }

    public function testShouldNotParseMultipleTagParameters()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        NameAddrHeader::parse(['sips:bob@offshore.biloxi.example.com;tag=sa42d23;tag=87db6v5']);
    }

    public function testShouldNotParseMultipleCustomParameters()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        NameAddrHeader::parse(['sips:bob@offshore.biloxi.example.com;custom=value;custom=again']);
    }

    public function testShouldNotParseUnmatchedQuotes()
    {
        $this->expectException(InvalidHeaderLineException::class);
        NameAddrHeader::parse(['"Bogus Bob <sips:bob@offshore.biloxi.example.com>']);
    }

    public function testShouldRenderWellFormedValue()
    {
        $header = new NameAddrHeader;
        $header->name = 'Bob';
        $header->uri = URI::parse('sip:bob@offshore.biloxi.example.com');
        $header->tag = 'sdf89vnc3';
        $header->params['unknown'] = 'parameter';

        $rendered = $header->render('To');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'To: "Bob" <sip:bob@offshore.biloxi.example.com>;tag=sdf89vnc3;unknown=parameter' . "\r\n",
            $rendered
        );
    }

    public function testShouldRenderWithoutName()
    {
        $header = new NameAddrHeader;
        $header->uri = URI::parse('sip:bob@offshore.biloxi.example.com');
        $header->tag = 'sdf89vnc3';
        $header->params['unknown'] = 'parameter';

        $rendered = $header->render('To');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'To: <sip:bob@offshore.biloxi.example.com>;tag=sdf89vnc3;unknown=parameter' . "\r\n",
            $rendered
        );
    }

    public function testShouldNotRenderWithoutURI()
    {
        $header = new NameAddrHeader;
        $header->name = 'Bob';

        $this->expectException(InvalidHeaderValueException::class);
        $header->render('To');
    }
}
