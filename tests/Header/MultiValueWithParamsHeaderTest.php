<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameter;
use RTCKit\SIP\Exception\InvalidHeaderValue;
use RTCKit\SIP\Header\MultiValueWithParamsHeader;
use RTCKit\SIP\Header\ValueWithParams;

use PHPUnit\Framework\TestCase;

class MultiValueWithParamsHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $header = MultiValueWithParamsHeader::parse(['text/plain;charset=utf-8, application/sdp;custom=something']);

        $this->assertNotNull($header);
        $this->assertInstanceOf(MultiValueWithParamsHeader::class, $header);
        $this->assertCount(2, $header->values);
        $this->assertEquals('text/plain', $header->values[0]->value);
        $this->assertEquals('utf-8', $header->values[0]->params['charset']);
        $this->assertEquals('application/sdp', $header->values[1]->value);
        $this->assertEquals('something', $header->values[1]->params['custom']);
    }

    public function testShouldNotParseMissingHeaderValue()
    {
        $this->expectException(InvalidHeaderLineException::class);
        MultiValueWithParamsHeader::parse([', application/sdp;custom=something']);
    }

    public function testShouldNotParseMissingHeaderParameterName()
    {
        $this->expectException(InvalidHeaderParameter::class);
        MultiValueWithParamsHeader::parse(['text/plain;=utf-8, application/sdp;custom=something']);
    }

    public function testShouldRenderWellFormedValue()
    {
        $header = new MultiValueWithParamsHeader;
        $header->values[0] = new ValueWithParams;
        $header->values[0]->value = 'text/plain';
        $header->values[0]->params['charset'] = 'utf-8';
        $header->values[1] = new ValueWithParams;
        $header->values[1]->value = 'application/sdp';
        $header->values[1]->params['custom'] = 'something';

        $rendered = $header->render('Accept');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("Accept: text/plain;charset=utf-8, application/sdp;custom=something\r\n", $rendered);
    }

    public function testShouldNotRenderMissingHeaderValue()
    {
        $header = new MultiValueWithParamsHeader;
        $header->values[0] = new ValueWithParams;

        $this->expectException(InvalidHeaderValue::class);
        $header->render('Accept');
    }

    public function testShouldNotRenderMissingHeaderParameterName()
    {
        $header = new MultiValueWithParamsHeader;
        $header->values[0] = new ValueWithParams;
        $header->values[0]->value = 'text/plain';
        $header->values[0]->params[''] = 'utf-8';

        $this->expectException(InvalidHeaderValue::class);
        $header->render('Accept');
    }
}
