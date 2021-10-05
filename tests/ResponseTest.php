<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidMessageStartLineException;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;
use RTCKit\SIP\Exception\InvalidStatusCodeException;
use RTCKit\SIP\Header\NameAddrHeader;

use PHPUnit\Framework\TestCase;

/**
 * SIP Response Tests
 */
class ResponseTest extends TestCase
{
    public function testShouldInstantiateWithoutArguments()
    {
        $response = new Response;

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShouldInstantiateWithCorrectStartLine()
    {
        $response = new Response('SIP/2.0 200 Alright');

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShouldFailWithoutVersion()
    {
        // Throws InvalidMessageStartLineException
        $this->expectException(InvalidMessageStartLineException::class);
        $response = new Response('200 Alright');
    }

    public function testShouldFailWithBadVersion()
    {
        // Throws InvalidProtocolVersionException
        $this->expectException(InvalidProtocolVersionException::class);
        $response = new Response('SIP/7.0 200 Alright');
    }

    public function testShouldFailWithOutOfBoundsCode()
    {
        // Throws InvalidStatusCodeException
        $this->expectException(InvalidStatusCodeException::class);
        new Response('SIP/2.0 799 Unknown');
    }

    public function testShouldFailWithoutCode()
    {
        // Throws InvalidProtocolVersionException
        $this->expectException(InvalidMessageStartLineException::class);
        $response = new Response('SIP/2.0 Alright');
    }

    public function testShouldFailWithoutReason()
    {
        // Throws InvalidMessageStartLineException
        $this->expectException(InvalidMessageStartLineException::class);
        $response = new Response('SIP/2.0 200');
    }

    public function testShouldRender()
    {
        $response = new Response();
        $response->code = 200;
        $response->reason = 'Cool';
        $response->from = new NameAddrHeader;
        $response->from->uri = URI::parse('sip:user@domain.com');
        $response->from->tag = 'rand0m';
        $response->to = new NameAddrHeader;
        $response->to->uri = URI::parse('sip:user@domain.com');
        $response->to->tag = 'T4g';

        $text = $response->render();

        $this->assertNotNull($text);
        $this->assertIsString($text);
        $this->assertEquals(
            'SIP/2.0 200 Cool' . "\r\n" .
            'From: <sip:user@domain.com>;tag=rand0m' . "\r\n" .
            'To: <sip:user@domain.com>;tag=T4g' . "\r\n" .
            "\r\n",
            $text
        );
    }

    public function testShouldRenderWithoutReasonWithKnownCode()
    {
        $response = new Response();
        $response->code = 200;
        $response->from = new NameAddrHeader;
        $response->from->uri = URI::parse('sip:user@domain.com');
        $response->from->tag = 'rand0m';
        $response->to = new NameAddrHeader;
        $response->to->uri = URI::parse('sip:user@domain.com');
        $response->to->tag = 'T4g';

        $text = $response->render();

        $this->assertNotNull($text);
        $this->assertIsString($text);
        $this->assertEquals(
            'SIP/2.0 200 OK' . "\r\n" .
            'From: <sip:user@domain.com>;tag=rand0m' . "\r\n" .
            'To: <sip:user@domain.com>;tag=T4g' . "\r\n" .
            "\r\n",
            $text
        );
    }

    public function testShouldNotRenderWithoutCode()
    {
        $response = new Response();

        // Throws InvalidStatusCodeException
        $this->expectException(InvalidStatusCodeException::class);
        $response->render();
    }

    public function testShouldNotRenderWithoutReasonForUnknownCode()
    {
        $response = new Response();
        $response->code = 699;

        // Throws InvalidStatusCodeException
        $this->expectException(InvalidStatusCodeException::class);
        $response->render();
    }

    public function testShouldNotRenderWithOutOfBoundsCode()
    {
        $response = new Response();
        $response->code = 700;

        // Throws InvalidStatusCodeException
        $this->expectException(InvalidStatusCodeException::class);
        $response->render();
    }

    public function testShouldNotRenderWithNegativeCode()
    {
        $response = new Response();
        $response->code = -200;

        // Throws InvalidStatusCodeException
        $this->expectException(InvalidStatusCodeException::class);
        $response->render();
    }
}
