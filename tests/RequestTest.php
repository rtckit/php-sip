<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidMessageStartLineException;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;
use RTCKit\SIP\Exception\InvalidRequestMethodException;
use RTCKit\SIP\Exception\InvalidRequestURIException;
use RTCKit\SIP\Header\NameAddrHeader;

use PHPUnit\Framework\TestCase;

/**
 * SIP Request Tests
 */
class RequestTest extends TestCase
{
    public function testShouldInstantiateWithoutArguments()
    {
        $request = new Request;

        $this->assertNotNull($request);
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testShouldInstantiateWithCorrectStartLine()
    {
        $request = new Request('INVITE sip:something.com SIP/2.0');

        $this->assertNotNull($request);
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testShouldFailWithoutVersion()
    {
        // Throws InvalidMessageStartLineException
        $this->expectException(InvalidMessageStartLineException::class);
        $request = new Request('INVITE sip:something.com');
    }

    public function testShouldFailWithBadVersion()
    {
        // Throws InvalidProtocolVersionException
        $this->expectException(InvalidProtocolVersionException::class);
        $request = new Request('INVITE sip:something.com SIP/7.0');
    }

    public function testShouldFailWithBadURI()
    {
        // Throws InvalidRequestURIException
        $this->expectException(InvalidRequestURIException::class);
        $request = new Request('INVITE <sip:something.com> SIP/2.0');
    }

    public function testShouldRender()
    {
        $request = new Request();
        $request->method = 'REGISTER';
        $request->uri = URI::parse('sip:user@domain.com');
        $request->from = new NameAddrHeader;
        $request->from->uri = URI::parse('sip:user@domain.com');
        $request->from->tag = 'rand0m';
        $request->to = new NameAddrHeader;
        $request->to->uri = URI::parse('sip:user@domain.com');

        $text = $request->render();

        $this->assertNotNull($text);
        $this->assertIsString($text);
        $this->assertEquals(
            'REGISTER sip:user@domain.com SIP/2.0' . "\r\n" .
            'From: <sip:user@domain.com>;tag=rand0m' . "\r\n" .
            'To: <sip:user@domain.com>' . "\r\n" .
            "\r\n",
            $text
        );
    }

    public function testShouldNotRenderWithoutMethod()
    {
        $request = new Request();
        $request->uri = URI::parse('sip:user@domain.com');

        // Throws InvalidRequestMethodException
        $this->expectException(InvalidRequestMethodException::class);
        $request->render();
    }

    public function testShouldNotRenderWithoutURI()
    {
        $request = new Request();
        $request->method = 'REGISTER';

        // Throws InvalidRequestURIException
        $this->expectException(InvalidRequestURIException::class);
        $request->render();
    }

    public function testShouldNotRenderWithHeadersInURI()
    {
        $request = new Request();
        $request->method = 'REGISTER';
        $request->uri = new URI;
        $request->uri->scheme = 'sip';
        $request->uri->user = 'user';
        $request->uri->host = 'domain.com';
        $request->uri->headers['Header'] = 'Value';

        // Throws InvalidRequestURIException
        $this->expectException(InvalidRequestURIException::class);
        $request->render();
    }
}
