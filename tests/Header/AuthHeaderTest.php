<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Header\AuthHeader;
use RTCKit\SIP\Header\AuthValue;

use PHPUnit\Framework\TestCase;

class AuthHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValues()
    {
        $auth = AuthHeader::parse([
            'Digest realm="sip.domain.net",domain="sip:sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5',
            'Basic YWxhZGRpbjpvcGVuc2VzYW1l',
        ]);

        $this->assertNotNull($auth);
        $this->assertInstanceOf(AuthHeader::class, $auth);
        $this->assertCount(2, $auth->values);
        $this->assertEquals('Digest', $auth->values[0]->scheme);
        $this->assertEquals('sip.domain.net', $auth->values[0]->realm);
        $this->assertEquals('sip:sip.domain.net', $auth->values[0]->domain);
        $this->assertEquals('auth', $auth->values[0]->qop);
        $this->assertEquals('7900f98e-3d80-4504-adbc-a61e5e040207', $auth->values[0]->nonce);
        $this->assertEquals(false, $auth->values[0]->stale);
        $this->assertEquals('MD5', $auth->values[0]->algorithm);
        $this->assertEquals('Basic', $auth->values[1]->scheme);
        $this->assertEquals('YWxhZGRpbjpvcGVuc2VzYW1l', $auth->values[1]->credentials);
    }

    public function testShouldParseVariousSpacingFormatting()
    {
        $scheme = 'Digest';
        $realm = 'sip.domain.net';
        $qop = 'auth';
        $cnonce = '7900f98e-3d80-4504-adbc-a61e5e040207';
        $stale = false;
        $algorithm = 'MD5';

        $auth = AuthHeader::parse([
            'Digest realm="sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5',
            '     Digest     realm="sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' Digest realm="sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5',
            ' Digest realm="sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' Digest realm   ="sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' Digest realm=    "sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' Digest realm="sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",   stale  =   FALSE  ,algorithm=      MD5 ',
            'Digest realm="sip.domain.net",qop="auth",cnonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE, algorithm=MD5',
            ' Digest realm=sip.domain.net,qop=auth,cnonce=7900f98e-3d80-4504-adbc-a61e5e040207,stale=FALSE,algorithm=MD5 ',
        ]);

        $count = count($auth->values);

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($scheme, $auth->values[$i]->scheme);
            $this->assertEquals($realm, $auth->values[$i]->realm);
            $this->assertEquals($qop, $auth->values[$i]->qop);
            $this->assertEquals($cnonce, $auth->values[$i]->cnonce);
            $this->assertEquals($stale, $auth->values[$i]->stale);
            $this->assertEquals($algorithm, $auth->values[$i]->algorithm);
        }

        $scheme = 'Basic';
        $credentials = 'YWxhZGRpbjpvcGVuc2VzYW1l';

        $auth = AuthHeader::parse([
            'Basic YWxhZGRpbjpvcGVuc2VzYW1l',
            'Basic YWxhZGRpbjpvcGVuc2VzYW1l  ',
            '  Basic YWxhZGRpbjpvcGVuc2VzYW1l',
            '  Basic    YWxhZGRpbjpvcGVuc2VzYW1l   ',
        ]);

        $count = count($auth->values);

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($scheme, $auth->values[$i]->scheme);
            $this->assertEquals($credentials, $auth->values[$i]->credentials);
        }
    }

    public function testShouldParseEnclosedCharacters()
    {
        $auth = AuthHeader::parse([
            'Digest qop="auth,auth-int"',
            'Digest response="QWxhZGRpbjpvcGVuIHNlc2FtZQ=="',
            'Digest response="enclosed\"quotes\"test"',
        ]);

        $this->assertEquals('auth,auth-int', $auth->values[0]->qop);
        $this->assertEquals('QWxhZGRpbjpvcGVuIHNlc2FtZQ==', $auth->values[1]->response);
        $this->assertEquals('enclosed"quotes"test', $auth->values[2]->response);
    }

    public function testShouldParseVariousNcValueFormatting()
    {
        $nc = hexdec('00000042');

        $auth = AuthHeader::parse([
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5,nc=42',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5,nc=00000042',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5,nc= "42"',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5,nc= "00000042"',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5,nc=   00000042   ',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5,nc=   42   ',
        ]);

        $count = count($auth->values);

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($nc, hexdec($auth->values[$i]->nc));
        }
    }

    public function testShouldParseVariousStaleValueFormatting()
    {
        $stale = false;

        $auth = AuthHeader::parse([
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=False,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=false,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FaLsE,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale= false,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale= false ,algorithm=MD5',
        ]);

        $count = count($auth->values);

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($stale, $auth->values[$i]->stale);
        }

        $stale = true;

        $auth = AuthHeader::parse([
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=TRUE,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=True,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=true,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=TrUe,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale= true,algorithm=MD5',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale= true ,algorithm=MD5',
        ]);

        $count = count($auth->values);

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($stale, $auth->values[$i]->stale);
        }
    }

    public function testShouldRenderWellFormedValues()
    {
        $digest = new AuthHeader;
        $digest->values[0] = new AuthValue;
        $digest->values[0]->scheme = 'Digest';
        $digest->values[0]->username = 'bob';
        $digest->values[0]->realm = 'sip.domain.net';
        $digest->values[0]->domain = 'sip:sip.domain.net';
        $digest->values[0]->nonce = 'a61e5e040207';
        $digest->values[0]->uri = 'sip:sip.domain.net';
        $digest->values[0]->response = 'KJHAFgHFIUAG';
        $digest->values[0]->stale = true;
        $digest->values[0]->algorithm = 'MD5';
        $digest->values[0]->cnonce = '7900f98e';
        $digest->values[0]->qop = 'auth-int';
        $digest->values[0]->nc = '42';
        $digest->values[0]->opaque = 'misc';

        $rendered = $digest->render('Authorization');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'Authorization: Digest username="bob",realm="sip.domain.net",domain="sip:sip.domain.net",nonce="a61e5e040207",uri="sip:sip.domain.net",response="KJHAFgHFIUAG",stale=TRUE,algorithm=MD5,cnonce="7900f98e",qop="auth-int",nc=42,opaque="misc"' . "\r\n",
            $rendered
        );

        $basic = new AuthHeader;
        $basic->values[0] = new AuthValue;
        $basic->values[0]->scheme = 'Basic';
        $basic->values[0]->credentials = 'MiScCreds';

        $rendered = $basic->render('Authorization');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'Authorization: Basic MiScCreds' . "\r\n",
            $rendered
        );

        $combined = new AuthHeader;
        $combined->values = [$digest->values[0], $basic->values[0]];

        $rendered = $combined->render('Authorization');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'Authorization: Digest username="bob",realm="sip.domain.net",domain="sip:sip.domain.net",nonce="a61e5e040207",uri="sip:sip.domain.net",response="KJHAFgHFIUAG",stale=TRUE,algorithm=MD5,cnonce="7900f98e",qop="auth-int",nc=42,opaque="misc"' . "\r\n" .
            'Authorization: Basic MiScCreds' . "\r\n",
            $rendered
        );
    }

    public function testShouldNotParseEmptyValue()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthHeader::parse(['']);
    }

    public function testShouldNotParseMissingParameters()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthHeader::parse(['Digest']);
    }

    public function testShouldNotParseValuelessParameters()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthHeader::parse(['Digest realm="sip.domain.net",username']);
    }

    public function testShouldNotParseValuelessParameters2()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthHeader::parse(['Digest realm="sip.domain.net",username=']);
    }

    public function testShouldNotParseMismatchedEnclosing()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthHeader::parse(['Digest realm="sip.domain.net']);
    }

    public function testShouldNotParseMismatchedEnclosing2()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthHeader::parse(['Digest realm="sip.domain.net"bogus']);
    }

    public function testShouldNotParseAlphaNcValues()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        AuthHeader::parse(['Digest nc=fortytwo']);
    }

    public function testShouldNotParseMixedNcValues()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        AuthHeader::parse(['Digest nc=O042']);
    }

    public function testShouldNotParseNonBoolStaleValues()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        AuthHeader::parse(['Digest stale=yes']);
    }

    public function testShouldNotRenderWithoutScheme()
    {
        $this->expectException(InvalidHeaderValueException::class);
        $auth = new AuthHeader;
        $auth->values[0] = new AuthValue;
        $auth->render('Authorize');
    }

    public function testShouldNotRenderNonDigestWithoutCredentials()
    {
        $this->expectException(InvalidHeaderValueException::class);
        $auth = new AuthHeader;
        $auth->values[0] = new AuthValue;
        $auth->values[0]->scheme = 'Basic';
        $auth->render('Authorize');
    }
}
