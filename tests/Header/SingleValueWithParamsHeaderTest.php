<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Header\SingleValueWithParamsHeader;

use PHPUnit\Framework\TestCase;

class SingleValueWithParamsHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $header = SingleValueWithParamsHeader::parse(['text/plain;charset=utf-8;custom=something']);

        $this->assertNotNull($header);
        $this->assertInstanceOf(SingleValueWithParamsHeader::class, $header);
        $this->assertEquals('text/plain', $header->value);
        $this->assertEquals('utf-8', $header->params['charset']);
        $this->assertEquals('something', $header->params['custom']);
    }

    public function testShouldNotParseMissingValue()
    {
        $this->expectException(InvalidHeaderLineException::class);
        SingleValueWithParamsHeader::parse(['']);
    }

    public function testShouldNotParseMultiValue()
    {
        $this->expectException(InvalidDuplicateHeaderException::class);
        SingleValueWithParamsHeader::parse(['text/plain;charset=utf-8;custom=something', 'application/sdp']);
    }

    public function testShouldNotParseMissingHeaderParameterName()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        SingleValueWithParamsHeader::parse(['text/plain;charset=utf-8;=something']);
    }

    public function testShouldRenderWellFormedValue()
    {
        $header = new SingleValueWithParamsHeader;
        $header->value = 'text/plain';
        $header->params['charset'] = 'utf-8';
        $header->params['custom'] = 'something';

        $rendered = $header->render('Content-Type');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("Content-Type: text/plain;charset=utf-8;custom=something\r\n", $rendered);
    }

    public function testShouldNotRenderMissingHeaderValue()
    {
        $header = new SingleValueWithParamsHeader;

        $this->expectException(InvalidHeaderValueException::class);
        $header->render('Accept');
    }

    public function testShouldNotRenderMissingHeaderParameterName()
    {
        $header = new SingleValueWithParamsHeader;
        $header->value = 'text/plain';
        $header->params[''] = 'utf-8';

        $this->expectException(InvalidHeaderValueException::class);
        $header->render('Accept');
    }
}
