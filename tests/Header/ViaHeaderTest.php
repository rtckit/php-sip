<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;
use RTCKit\SIP\Header\ViaHeader;
use RTCKit\SIP\Header\ViaValue;

use PHPUnit\Framework\TestCase;

class ViaHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValues()
    {
        $via = ViaHeader::parse([
            'SIP/2.0/UDP 178.73.76.230:5060;branch=z9hG4bKiokioukju908',
            'SIP/2.0/UDP 112.68.155.4:5060;branch=z9hG4bK34ghi7ab04;custom=parameter',
            'SIP/2.0/TCP 192.0.2.4:5060;branch=z9hG4bKnashds7',
        ]);

        $this->assertNotNull($via);
        $this->assertInstanceOf(ViaHeader::class, $via);
        $this->assertCount(3, $via->values);
        $this->assertEquals('SIP', $via->values[0]->protocol);
        $this->assertEquals('2.0', $via->values[0]->version);
        $this->assertEquals('UDP', $via->values[0]->transport);
        $this->assertEquals('178.73.76.230:5060', $via->values[0]->host);
        $this->assertEquals('z9hG4bKiokioukju908', $via->values[0]->branch);
        $this->assertEquals('SIP', $via->values[1]->protocol);
        $this->assertEquals('2.0', $via->values[1]->version);
        $this->assertEquals('UDP', $via->values[1]->transport);
        $this->assertEquals('112.68.155.4:5060', $via->values[1]->host);
        $this->assertEquals('z9hG4bK34ghi7ab04', $via->values[1]->branch);
        $this->assertEquals('parameter', $via->values[1]->params['custom']);
        $this->assertEquals('SIP', $via->values[2]->protocol);
        $this->assertEquals('2.0', $via->values[2]->version);
        $this->assertEquals('TCP', $via->values[2]->transport);
        $this->assertEquals('192.0.2.4:5060', $via->values[2]->host);
        $this->assertEquals('z9hG4bKnashds7', $via->values[2]->branch);
    }

    public function testShouldNotParseInvalidValue()
    {
        $this->expectException(InvalidHeaderLineException::class);
        ViaHeader::parse(['178.73.76.230:5060;branch=z9hG4bKiokioukju908']);
    }

    public function testShouldNotParseInvalidProtocol()
    {
        $this->expectException(InvalidProtocolVersionException::class);
        ViaHeader::parse(['FTP/2.0/TCP 178.73.76.230:5060;branch=z9hG4bKiokioukju908']);
    }

    public function testShouldNotParseInvalidProtocolVersion()
    {
        $this->expectException(InvalidProtocolVersionException::class);
        ViaHeader::parse(['SIP/2.1/TCP 178.73.76.230:5060;branch=z9hG4bKiokioukju908']);
    }

    public function testShouldNotParseMissingHost()
    {
        $this->expectException(InvalidHeaderLineException::class);
        ViaHeader::parse(['SIP/2.0/TCP']);
    }

    public function testShouldNotParseEmptyReceivedParameter()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ViaHeader::parse(['SIP/2.0/TCP 178.73.76.230:5060;branch=z9hG4bKiokioukju908;received']);
    }

    public function testShouldNotParseNonIPReceivedParameter()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ViaHeader::parse(['SIP/2.0/TCP 178.73.76.230:5060;branch=z9hG4bKiokioukju908;received=some.fqdn.com']);
    }

    public function testShouldNotParseNonNumericRPortParameter()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ViaHeader::parse(['SIP/2.0/TCP 178.73.76.230:5060;branch=z9hG4bKiokioukju908;rport=onethousandtwentyfour']);
    }

    public function testShouldNotParseLargeRPortParameter()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ViaHeader::parse(['SIP/2.0/TCP 178.73.76.230:5060;branch=z9hG4bKiokioukju908;rport=318272']);
    }

    public function testShouldNotParseEmptyParameterNames()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ViaHeader::parse(['SIP/2.0/TCP 178.73.76.230:5060;branch=z9hG4bKiokioukju908;=something']);
    }

    public function testShouldRenderWellFormedValues()
    {
        $via = new ViaHeader;
        $via->values[0] = new ViaValue;
        $via->values[0]->protocol = 'SIP';
        $via->values[0]->version = '2.0';
        $via->values[0]->transport = 'UDP';
        $via->values[0]->host = '192.0.2.4:5060';
        $via->values[0]->branch = 'z9hG4bKnashds7';
        $via->values[0]->received = '64.52.36.12';
        $via->values[0]->rport = 1025;
        $via->values[0]->params['custom'] = 'something';

        $rendered = $via->render('Via');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'Via: SIP/2.0/UDP 192.0.2.4:5060;branch=z9hG4bKnashds7;received=64.52.36.12;rport=1025;custom=something' . "\r\n",
            $rendered
        );
    }

    public function testShouldRenderEmptyRPort()
    {
        $via = new ViaHeader;
        $via->values[0] = new ViaValue;
        $via->values[0]->protocol = 'SIP';
        $via->values[0]->version = '2.0';
        $via->values[0]->transport = 'UDP';
        $via->values[0]->host = '192.0.2.4:5060';
        $via->values[0]->branch = 'z9hG4bKnashds7';
        $via->values[0]->rport = 0;
        $via->values[0]->params['custom'] = 'something';

        $rendered = $via->render('Via');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'Via: SIP/2.0/UDP 192.0.2.4:5060;branch=z9hG4bKnashds7;rport;custom=something' . "\r\n",
            $rendered
        );
    }

    public function testShouldNotRenderMissingProtocol()
    {
        $via = new ViaHeader;
        $via->values[0] = new ViaValue;
        $via->values[0]->version = '2.0';
        $via->values[0]->transport = 'UDP';
        $via->values[0]->host = '192.0.2.4:5060';
        $via->values[0]->branch = 'z9hG4bKnashds7';

        $this->expectException(InvalidHeaderValueException::class);
        $via->render('Via');
    }

    public function testShouldNotRenderMissingProtocolVersion()
    {
        $via = new ViaHeader;
        $via->values[0] = new ViaValue;
        $via->values[0]->protocol = 'SIP';
        $via->values[0]->transport = 'UDP';
        $via->values[0]->host = '192.0.2.4:5060';
        $via->values[0]->branch = 'z9hG4bKnashds7';

        $this->expectException(InvalidHeaderValueException::class);
        $via->render('Via');
    }

    public function testShouldNotRenderMissingTransport()
    {
        $via = new ViaHeader;
        $via->values[0] = new ViaValue;
        $via->values[0]->protocol = 'SIP';
        $via->values[0]->version = '2.0';
        $via->values[0]->host = '192.0.2.4:5060';
        $via->values[0]->branch = 'z9hG4bKnashds7';

        $this->expectException(InvalidHeaderValueException::class);
        $via->render('Via');
    }

    public function testShouldNotRenderMissingHost()
    {
        $via = new ViaHeader;
        $via->values[0] = new ViaValue;
        $via->values[0]->protocol = 'SIP';
        $via->values[0]->version = '2.0';
        $via->values[0]->transport = 'UDP';
        $via->values[0]->branch = 'z9hG4bKnashds7';

        $this->expectException(InvalidHeaderValueException::class);
        $via->render('Via');
    }
}
