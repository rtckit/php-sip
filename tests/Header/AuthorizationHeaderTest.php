<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Auth\Digest\Response;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Header\AuthorizationHeader;
use RTCKit\SIP\Header\AuthValue;

use PHPUnit\Framework\TestCase;

class AuthorizationHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValues()
    {
        $auth = AuthorizationHeader::parse([
            'Digest username="alice", realm="sip.domain.net", nonce="xPgHm7ito95w8je2FGvPS-", cnonce="jdudlKi/EjqLMlSgUFIFyQ", algorithm=MD5, uri="sip:sip.domain.net:5566;transport=udp", response="d1ffd656b679aa3a0ace1c648b36ab7d", qop=auth, nc=00000001',
            'Basic d1ffd656b679aa3a0ace1c648b36ab7d',
        ]);

        $this->assertNotNull($auth);
        $this->assertInstanceOf(AuthorizationHeader::class, $auth);
        $this->assertCount(2, $auth->values);
        $this->assertEquals('digest', $auth->values[0]->scheme);
        $this->assertEquals('sip.domain.net', $auth->values[0]->params->realm);
        $this->assertEquals('xPgHm7ito95w8je2FGvPS-', $auth->values[0]->params->nonce);
        $this->assertEquals('jdudlKi/EjqLMlSgUFIFyQ', $auth->values[0]->params->cnonce);
        $this->assertEquals('MD5', $auth->values[0]->params->algorithm);
        $this->assertEquals('sip:sip.domain.net:5566;transport=udp', $auth->values[0]->params->uri);
        $this->assertEquals('d1ffd656b679aa3a0ace1c648b36ab7d', $auth->values[0]->params->response);
        $this->assertEquals('auth', $auth->values[0]->params->qop);
        $this->assertEquals('00000001', $auth->values[0]->params->nc);

        /* Basic authentication scheme has been obsoleted by RFC 3261, now it's handled as an opaque scheme */
        $this->assertEquals('basic', $auth->values[1]->scheme);
        $this->assertEquals('d1ffd656b679aa3a0ace1c648b36ab7d', $auth->values[1]->params->verbatim);
    }

    public function testShouldParseEnclosedCharacters()
    {
        $auth = AuthorizationHeader::parse([
            'Digest username="QWxhZGRpbjpvcGVuIHNlc2FtZQ=="',
            'Digest username="enclosed\"quotes\"test"',
        ]);

        $this->assertEquals('QWxhZGRpbjpvcGVuIHNlc2FtZQ==', $auth->values[0]->params->username);
        $this->assertEquals('enclosed"quotes"test', $auth->values[1]->params->username);
    }

    public function testShouldNotParseAlphaNcValues()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        AuthorizationHeader::parse(['Digest nc=fortytwo']);
    }

    public function testShouldNotParseMixedNcValues()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        AuthorizationHeader::parse(['Digest nc=O042']);
    }

    public function testShouldNotParseNonHexResponseValues()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        AuthorizationHeader::parse(['Digest response="deadbeef_-~"']);
    }
}
