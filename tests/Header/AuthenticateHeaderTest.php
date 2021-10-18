<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Auth\OpaqueParams;
use RTCKit\SIP\Auth\Digest\ChallengeParams;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Header\AuthenticateHeader;
use RTCKit\SIP\Header\AuthValue;

use PHPUnit\Framework\TestCase;

class AuthenticateHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValues()
    {
        $auth = AuthenticateHeader::parse([
            'Digest realm="sip.domain.net",domain="sip:sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=TRUE,algorithm=MD5',
            'Basic realm="Access to the staging site"',
        ]);

        $this->assertNotNull($auth);
        $this->assertInstanceOf(AuthenticateHeader::class, $auth);
        $this->assertCount(2, $auth->values);
        $this->assertEquals('digest', $auth->values[0]->scheme);
        $this->assertEquals('sip.domain.net', $auth->values[0]->params->realm);
        $this->assertEquals('sip:sip.domain.net', $auth->values[0]->params->domain);
        $this->assertEquals('auth', $auth->values[0]->params->qop[0]);
        $this->assertEquals('7900f98e-3d80-4504-adbc-a61e5e040207', $auth->values[0]->params->nonce);
        $this->assertEquals(true, $auth->values[0]->params->stale);
        $this->assertEquals('MD5', $auth->values[0]->params->algorithm);

        /* Basic authentication scheme has been obsoleted by RFC 3261, now it's handled as an opaque scheme */
        $this->assertEquals('basic', $auth->values[1]->scheme);
        $this->assertEquals('realm="Access to the staging site"', $auth->values[1]->params->verbatim);
    }

    public function testShouldParseVariousSpacingFormatting()
    {
        $scheme = 'digest';
        $realm = 'sip.domain.net';
        $qop = 'auth';
        $nonce = '7900f98e-3d80-4504-adbc-a61e5e040207';
        $stale = false;
        $algorithm = 'MD5';

        $auth = AuthenticateHeader::parse([
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5',
            '     Digest     realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5',
            ' Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' DiGeSt realm   ="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' Digest realm=    "sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE,algorithm=MD5 ',
            ' Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",   stale  =   FALSE  ,algorithm=      MD5 ',
            'Digest realm="sip.domain.net",qop="auth",nonce="7900f98e-3d80-4504-adbc-a61e5e040207",stale=FALSE, algorithm=MD5',
            ' Digest realm=sip.domain.net,qop=auth,nonce=7900f98e-3d80-4504-adbc-a61e5e040207,stale=FALSE,algorithm=MD5 ',
        ]);

        $count = count($auth->values);

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($scheme, $auth->values[$i]->scheme);
            $this->assertEquals($realm, $auth->values[$i]->params->realm);
            $this->assertEquals($qop, $auth->values[$i]->params->qop[0]);
            $this->assertEquals($nonce, $auth->values[$i]->params->nonce);
            $this->assertEquals($stale, $auth->values[$i]->params->stale);
            $this->assertEquals($algorithm, $auth->values[$i]->params->algorithm);
        }

        $scheme = 'basic';
        $verbatim = 'realm="Access to the staging site"';

        $auth = AuthenticateHeader::parse([
            'Basic realm="Access to the staging site"',
            'BaSiC realm="Access to the staging site"  ',
            '  Basic realm="Access to the staging site"',
            '  Basic    realm="Access to the staging site"   ',
        ]);

        $count = count($auth->values);

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($scheme, $auth->values[$i]->scheme);
            $this->assertEquals($verbatim, $auth->values[$i]->params->verbatim);
        }
    }

    public function testShouldRenderWellFormedValues()
    {
        $digest = new AuthenticateHeader;
        $digest->values[0] = new AuthValue;
        $digest->values[0]->scheme = 'Digest';
        $digest->values[0]->params = new ChallengeParams;
        $digest->values[0]->params->realm = 'sip.domain.net';
        $digest->values[0]->params->domain = 'sip:sip.domain.net';
        $digest->values[0]->params->nonce = 'a61e5e040207';
        $digest->values[0]->params->stale = true;
        $digest->values[0]->params->algorithm = 'MD5';
        $digest->values[0]->params->qop = ['auth', 'auth-int'];
        $digest->values[0]->params->opaque = 'misc';

        $rendered = $digest->render('WWW-Authenticate');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'WWW-Authenticate: Digest realm="sip.domain.net",algorithm=MD5,nonce="a61e5e040207",opaque="misc",domain="sip:sip.domain.net",stale=TRUE,qop="auth,auth-int"' . "\r\n",
            $rendered
        );

        $digest = new AuthenticateHeader;
        $digest->values[0] = new AuthValue;
        $digest->values[0]->scheme = 'ExoticScheme';
        $digest->values[0]->params = new OpaqueParams;
        $digest->values[0]->params->verbatim = 'd02acf84-bc19-4722-8370-faade42bb583';

        $rendered = $digest->render('WWW-Authenticate');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'WWW-Authenticate: ExoticScheme d02acf84-bc19-4722-8370-faade42bb583' . "\r\n",
            $rendered
        );
    }

    public function testShouldParseEnclosedCharacters()
    {
        $auth = AuthenticateHeader::parse(['Digest qop="auth,auth-int"']);

        $this->assertEquals(['auth', 'auth-int'], $auth->values[0]->params->qop);
    }

    public function testShouldNotParseEmptyValue()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthenticateHeader::parse(['']);
    }

    public function testShouldNotParseMissingParameters()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthenticateHeader::parse(['Digest']);
    }

    public function testShouldNotParseValuelessParameters()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthenticateHeader::parse(['Digest realm="sip.domain.net",domain']);
    }

    public function testShouldNotParseValuelessParameters2()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthenticateHeader::parse(['Digest realm="sip.domain.net",domain=']);
    }

    public function testShouldNotParseMismatchedEnclosing()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthenticateHeader::parse(['Digest realm="sip.domain.net']);
    }

    public function testShouldNotParseMismatchedEnclosing2()
    {
        $this->expectException(InvalidHeaderLineException::class);
        AuthenticateHeader::parse(['Digest realm="sip.domain.net"bogus']);
    }

    public function testShouldNotParseNonBoolStaleValues()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        AuthenticateHeader::parse(['Digest stale=yes']);
    }

    public function testShouldNotRenderWithoutScheme()
    {
        $this->expectException(InvalidHeaderValueException::class);
        $auth = new AuthenticateHeader;
        $auth->values[0] = new AuthValue;
        $auth->render('Authorize');
    }

    public function testShouldNotRenderNonDigestWithoutParameters()
    {
        $this->expectException(InvalidHeaderValueException::class);
        $auth = new AuthenticateHeader;
        $auth->values[0] = new AuthValue;
        $auth->values[0]->scheme = 'Basic';
        $auth->render('Authorize');
    }
}
