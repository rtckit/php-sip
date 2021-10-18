<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Auth\Digest\ChallengeParams;
use RTCKit\SIP\Auth\Digest\ResponseParams;
use RTCKit\SIP\Exception\InvalidBodyLengthException;
use RTCKit\SIP\Exception\InvalidCSeqValueException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderSectionException;
use RTCKit\SIP\Exception\InvalidScalarValueException;
use RTCKit\SIP\Exception\SIPException;
use RTCKit\SIP\Header\AuthValue;
use RTCKit\SIP\Header\AuthenticateHeader;
use RTCKit\SIP\Header\AuthorizationHeader;
use RTCKit\SIP\Header\CallIdHeader;
use RTCKit\SIP\Header\ContactHeader;
use RTCKit\SIP\Header\ContactValue;
use RTCKit\SIP\Header\CSeqHeader;
use RTCKit\SIP\Header\Header;
use RTCKit\SIP\Header\FromHeader;
use RTCKit\SIP\Header\MaxForwardsHeader;
use RTCKit\SIP\Header\MultiValueHeader;
use RTCKit\SIP\Header\MultiValueWithParamsHeader;
use RTCKit\SIP\Header\NameAddrHeader;
use RTCKit\SIP\Header\ScalarHeader;
use RTCKit\SIP\Header\SingleValueWithParamsHeader;
use RTCKit\SIP\Header\ValueWithParams;
use RTCKit\SIP\Header\ViaHeader;
use RTCKit\SIP\Header\ViaValue;

use PHPUnit\Framework\TestCase;

/**
 * SIP Message Tests
 */
class MessageTest extends TestCase
{
    public function testShouldParseWellFormedMessages()
    {
        $request = Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'Via: SIP/2.0/UDP 192.0.2.4:5060;branch=z9hG4bKnashds7' . "\r\n" .
            'v: SIP/2.0/UDP 178.73.76.230:5060;branch=z9hG4bKiokioukju908' . "\r\n" .
            'From: "Alice" <sip:alice@atlanta.example.com>;tag=9fxced76sl' . "\r\n" .
            'To: "Bob" <sip:bob@biloxi.com>' . "\r\n" .
            'Contact: <sip:user@domain.com>' . "\r\n" .
            'Call-ID: none' . "\r\n" .
            'CSeq: 7 METHOD' . "\r\n" .
            'Max-Forwards: 69' . "\r\n" .
            'Content-Length: 4' . "\r\n" .
            'Expires: 1800' . "\r\n" .
            'Min-Expires: 600' . "\r\n" .
            'Retry-After: 120' . "\r\n" .
            'Timestamp: 84' . "\r\n" .
            'Content-Type: mime/type' . "\r\n" .
            'Accept-Encoding: gzip' . "\r\n" .
            'Allow: METHOD' . "\r\n" .
            'Allow-Events: ring' . "\r\n" .
            'Content-Encoding: gzip' . "\r\n" .
            'In-Reply-To: 8080@atlanta.bell-tel.com' . "\r\n" .
            'Require: 100rel' . "\r\n" .
            'Supported: replaces' . "\r\n" .
            'Unsupported: 101rel' . "\r\n" .
            'Proxy-Require: sec‑agree' . "\r\n" .
            'Accept: mime/type' . "\r\n" .
            'Accept-Language: en' . "\r\n" .
            'Call-Info: <http://www.example.com/alice/photo.jpg>;purpose=icon' . "\r\n" .
            'Content-Language: es' . "\r\n" .
            'Reply-To: "Bob" <sip:bob@biloxi.com>' . "\r\n" .
            'Alert-Info: <file://external.ring.pcm>' . "\r\n" .
            'Authentication-Info: nextnonce="47364c23432d2e131a5fb210812c"' . "\r\n" .
            'Authorization: Digest username="bob", realm="atlanta.example.com", nonce="ea9c8e88df84f1cec4341ae6cbe5a359", opaque="", uri="sips:ss2.biloxi.example.com", response="dfe56131d1958046689d83306477ecc"' . "\r\n" .
            'Date: Thu, 21 Feb 2002 13:02:03 GMT' . "\r\n" .
            'Error-Info: <sip:screen-failure-term-ann@annoucement.example.com>' . "\r\n" .
            'Proxy-Authenticate: Digest realm="atlanta.example.com", qop="auth", nonce="f84f1cec41e6cbe5aea9c8e88d359", opaque="", stale=FALSE, algorithm=MD5' . "\r\n" .
            'Proxy-Authorization: Digest username="alice", realm="atlanta.example.com", nonce="wf84f1ceczx41ae6cbe5aea9c8e88d359", opaque="", uri="sip:bob@biloxi.example.com", response="42ce3cef44b22f50c6a6071bc8"' . "\r\n" .
            'Record-Route: <sip:ss2.biloxi.example.com;lr>' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Organization: RTCKit' . "\r\n" .
            'Priority: urgent' . "\r\n" .
            'Route: <sip:ss1.atlanta.example.com;lr>' . "\r\n" .
            'Subject: Hello' . "\r\n" .
            'User-Agent: RTCKit\\SIP' . "\r\n" .
            'Warning: 301 isi.edu "Incompatible network address type \'E.164\'"' . "\r\n" .
            'WWW-Authenticate: Digest realm="atlanta.example.com", qop="auth", nonce="84f1c1ae6cbe5ua9c8e88dfa3ecm3459",opaque="", stale=FALSE, algorithm=MD5' . "\r\n" .
            'X-Custom-Header: Something truly important' . "\r\n" .
            "\r\n" .
            'body'
        );

        $this->assertNotNull($request);
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testShouldDisinguishResponsesFromRequests()
    {
        $request = Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq: 7 METHOD' . "\r\n" .
            "\r\n"
        );

        $this->assertNotNull($request);
        $this->assertInstanceOf(Request::class, $request);

        $response = Message::parse(
            'SIP/2.0 200 OK' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq: 7 METHOD' . "\r\n" .
            "\r\n"
        );

        $this->assertNotNull($response);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShouldParseMultilineSingleHeaderValue()
    {
        $request = Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'CSeq: 7' . "\r\n" .
            '  METHOD' . "\r\n" .
            "\r\n"
        );

        $this->assertNotNull($request);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals(7, $request->cSeq->sequence);
        $this->assertEquals('METHOD', $request->cSeq->method);
    }

    public function testShouldNotParseIllIndentedHeaders()
    {
        // Throws InvalidHeaderLineException
        $this->expectException(InvalidHeaderLineException::class);
        Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            '  From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            "\r\n"
        );
    }

    public function testShouldNotParseUndelimitedHeaders()
    {
        // Throws InvalidHeaderLineException
        $this->expectException(InvalidHeaderLineException::class);
        Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq 7 METHOD' . "\r\n" .
            "\r\n"
        );
    }

    public function testShouldNotParseEmptyHeaderNames()
    {
        // Throws InvalidHeaderLineException
        $this->expectException(InvalidHeaderLineException::class);
        Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            ':  7 METHOD' . "\r\n" .
            "\r\n"
        );
    }

    public function testShouldNotParseMissingCRLFAfterHeaders()
    {
        // Throws InvalidHeaderSectionException
        $this->expectException(InvalidHeaderSectionException::class);
        Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq: 7 METHOD'
        );
    }

    public function testShouldNotParseMismatchingCSeqMethods()
    {
        // Throws InvalidCSeqValueException
        $this->expectException(InvalidCSeqValueException::class);
        Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq: 7 OTHER' . "\r\n" .
            "\r\n"
        );
    }

    public function testShouldNotParseNegativeContentLength()
    {
        // Throws InvalidScalarValueException
        $this->expectException(InvalidScalarValueException::class);
        Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq: 7 METHOD' . "\r\n" .
            'Content-Length: -9' . "\r\n" .
            "\r\n"
        );
    }

    public function testShouldNotParseShortBody()
    {
        // Throws InvalidBodyLengthException
        $this->expectException(InvalidBodyLengthException::class);
        Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq: 7 METHOD' . "\r\n" .
            'Content-Length: 16' . "\r\n" .
            "\r\n" .
            "Not enough"
        );
    }

    public function testShouldAssignStubsToThrownExceptions()
    {
        try {
            Message::parse(
                'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
                'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
                'CSeq: 7 OTHER' . "\r\n" .
                "\r\n"
            );
        } catch (SIPException $e) {
            $stub = $e->getStub();

            $this->assertNotNull($stub);
            $this->assertInstanceOf(Message::class, $stub);

            /* Furthermore, if it looks like a request, it should be instantiated as such */
            $this->assertInstanceOf(Request::class, $stub);
        }
    }

    public function testShouldParseMessageWithNoiseAfterBody()
    {
        $request = Message::parse(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'From: Alice <sip:alice@atlanta.com>;tag=9fxced76sl' . "\r\n" .
            'CSeq: 7 METHOD' . "\r\n" .
            'Content-Length: 4' . "\r\n" .
            "\r\n" .
            "body; this portion is just spurious noise"
        );

        $this->assertNotNull($request);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals('body', $request->body);
    }

    public function testShouldRenderWellFormedRequests()
    {
        $request = new Request;

        $request->method = 'METHOD';
        $request->uri = new URI;
        $request->uri->scheme = 'sip';
        $request->uri->user = 'user';
        $request->uri->host = 'nowhere.com';
        $request->version = 'SIP/2.0';

        $request->via = new ViaHeader;
        $request->via->values[0] = new ViaValue;
        $request->via->values[0]->protocol = 'SIP';
        $request->via->values[0]->version = '2.0';
        $request->via->values[0]->transport = 'UDP';
        $request->via->values[0]->host = '192.0.2.4:5060';
        $request->via->values[0]->branch = 'z9hG4bKnashds7';

        $request->from = new NameAddrHeader;
        $request->from->uri = URI::parse('sip:alice@atlanta.example.com');
        $request->from->name = 'Alice';
        $request->from->tag = '9fxced76sl';

        $request->to = new NameAddrHeader;
        $request->to->uri = URI::parse('sip:bob@biloxi.com');
        $request->to->name = 'Bob';

        $request->contact = new ContactHeader;
        $request->contact->values[0] = new ContactValue;
        $request->contact->values[0]->uri = URI::parse('sip:user@domain.com');

        $request->callId = new CallIdHeader;
        $request->callId->value = '715';

        $request->cSeq = new CSeqHeader;
        $request->cSeq->sequence = 7;
        $request->cSeq->method = 'METHOD';

        $request->maxForwards = new ScalarHeader;
        $request->maxForwards->value = 69;

        $request->contentLength = new ScalarHeader;
        $request->contentLength->value = 4;

        $request->expires = new ScalarHeader;
        $request->expires->value = 1800;

        $request->minExpires = new ScalarHeader;
        $request->minExpires->value = 600;

        $request->retryAfter = new ScalarHeader;
        $request->retryAfter->value = 120;

        $request->timestamp = new ScalarHeader;
        $request->timestamp->value = 84;

        $request->contentType = new SingleValueWithParamsHeader;
        $request->contentType->value = 'mime/type';

        $request->acceptEncoding = new MultiValueHeader;
        $request->acceptEncoding->values[] = 'gzip';

        $request->allow = new MultiValueHeader;
        $request->allow->values[] = 'gzip';

        $request->allowEvents = new MultiValueHeader;
        $request->allowEvents->values[] = 'ring';

        $request->contentEncoding = new MultiValueHeader;
        $request->contentEncoding->values[] = 'gzip';

        $request->inReplyTo = new MultiValueHeader;
        $request->inReplyTo->values[] = '8080@atlanta.bell-tel.com';

        $request->require = new MultiValueHeader;
        $request->require->values[] = '100rel';

        $request->supported = new MultiValueHeader;
        $request->supported->values[] = 'replaces';

        $request->unsupported = new MultiValueHeader;
        $request->unsupported->values[] = '101rel';

        $request->proxyRequire = new MultiValueHeader;
        $request->proxyRequire->values[] = 'sec-agree';

        $request->accept = new MultiValueWithParamsHeader;
        $request->accept->values[0] = new ValueWithParams;
        $request->accept->values[0]->value = 'mime/type';

        $request->acceptLanguage = new MultiValueWithParamsHeader;
        $request->acceptLanguage->values[0] = new ValueWithParams;
        $request->acceptLanguage->values[0]->value = 'en';

        $request->callInfo = new MultiValueWithParamsHeader;
        $request->callInfo->values[0] = new ValueWithParams;
        $request->callInfo->values[0]->value = '<http://www.example.com/alice/photo.jpg>';
        $request->callInfo->values[0]->params['purpose'] = 'icon';

        $request->contentLanguage = new MultiValueWithParamsHeader;
        $request->contentLanguage->values[0] = new ValueWithParams;
        $request->contentLanguage->values[0]->value = 'es';

        $request->replyTo = new NameAddrHeader;
        $request->replyTo->uri = URI::parse('sip:bob@biloxi.com');
        $request->replyTo->name = 'Bob';

        $request->alertInfo = new Header;
        $request->alertInfo->values[] = '<file://external.ring.pcm>';

        $request->authenticationInfo = new Header;
        $request->authenticationInfo->values[] = 'nextnonce="47364c23432d2e131a5fb210812c"';

        $request->authorization = new AuthorizationHeader;
        $request->authorization->values[0] = new AuthValue;
        $request->authorization->values[0]->scheme = 'Digest';
        $request->authorization->values[0]->params = new ResponseParams;
        $request->authorization->values[0]->params->username = 'bob';
        $request->authorization->values[0]->params->realm = 'atlanta.example.com';
        $request->authorization->values[0]->params->cnonce = 'ea9c8e88df84f1cec4341ae6cbe5a359';
        $request->authorization->values[0]->params->nc = '00000042';
        $request->authorization->values[0]->params->qop = 'auth';
        $request->authorization->values[0]->params->opaque = '';
        $request->authorization->values[0]->params->uri = 'sips:ss2.biloxi.example.com';
        $request->authorization->values[0]->params->response = 'dfe56131d1958046689d83306477ecc';

        $request->date = new Header;
        $request->date->values[] = 'Thu, 21 Feb 2002 13:02:03 GMT';

        $request->errorInfo = new Header;
        $request->errorInfo->values[] = '<sip:screen-failure-term-ann@annoucement.example.com>';

        $request->proxyAuthenticate = new AuthenticateHeader;
        $request->proxyAuthenticate->values[0] = new AuthValue;
        $request->proxyAuthenticate->values[0]->scheme = 'Digest';
        $request->proxyAuthenticate->values[0]->params = new ChallengeParams;
        $request->proxyAuthenticate->values[0]->params->realm = 'atlanta.example.com';
        $request->proxyAuthenticate->values[0]->params->qop = ['auth'];
        $request->proxyAuthenticate->values[0]->params->nonce = 'f84f1cec41e6cbe5aea9c8e88d359';
        $request->proxyAuthenticate->values[0]->params->opaque = '';
        $request->proxyAuthenticate->values[0]->params->stale = false;
        $request->proxyAuthenticate->values[0]->params->algorithm = 'MD5';

        $request->proxyAuthorization = new AuthorizationHeader;
        $request->proxyAuthorization->values[0] = new AuthValue;
        $request->proxyAuthorization->values[0]->scheme = 'Digest';
        $request->proxyAuthorization->values[0]->params = new ResponseParams;
        $request->proxyAuthorization->values[0]->params->username = 'alice';
        $request->proxyAuthorization->values[0]->params->realm = 'atlanta.example.com';
        $request->proxyAuthorization->values[0]->params->cnonce = 'wf84f1ceczx41ae6cbe5aea9c8e88d359';
        $request->proxyAuthorization->values[0]->params->opaque = '';
        $request->proxyAuthorization->values[0]->params->uri = 'sip:bob@biloxi.example.com';
        $request->proxyAuthorization->values[0]->params->response = '42ce3cef44b22f50c6a6071bc8';

        $request->recordRoute = new Header;
        $request->recordRoute->values[] = '<sip:ss2.biloxi.example.com;lr>';

        $request->mimeVersion = new Header;
        $request->mimeVersion->values[] = '1.0';

        $request->organization = new Header;
        $request->organization->values[] = 'RTCKit';

        $request->priority = new Header;
        $request->priority->values[] = 'urgent';

        $request->route = new Header;
        $request->route->values[] = '<sip:ss1.atlanta.example.com;lr>';

        $request->subject = new Header;
        $request->subject->values[] = 'Hello';

        $request->userAgent = new Header;
        $request->userAgent->values[] = 'RTCKit\\SIP';

        $request->warning = new Header;
        $request->warning->values[] = '301 isi.edu "Incompatible network address type \'E.164\'"';

        $request->wwwAuthenticate = new AuthenticateHeader;
        $request->wwwAuthenticate->values[0] = new AuthValue;
        $request->wwwAuthenticate->values[0]->scheme = 'Digest';
        $request->wwwAuthenticate->values[0]->params = new ChallengeParams;
        $request->wwwAuthenticate->values[0]->params->realm = 'atlanta.example.com';
        $request->wwwAuthenticate->values[0]->params->qop = ['auth'];
        $request->wwwAuthenticate->values[0]->params->nonce = '84f1c1ae6cbe5ua9c8e88dfa3ecm3459';
        $request->wwwAuthenticate->values[0]->params->opaque = '';
        $request->wwwAuthenticate->values[0]->params->stale = false;
        $request->wwwAuthenticate->values[0]->params->algorithm = 'MD5';

        $request->extraHeaders['X-Custom-Header'] = new Header;
        $request->extraHeaders['X-Custom-Header']->values[0] = 'Something truly important';

        $rendered = $request->render();

        $this->assertIsString($rendered);
        $this->assertEquals(
            'METHOD sip:user@nowhere.com SIP/2.0' . "\r\n" .
            'Via: SIP/2.0/UDP 192.0.2.4:5060;branch=z9hG4bKnashds7' . "\r\n" .
            'From: "Alice" <sip:alice@atlanta.example.com>;tag=9fxced76sl' . "\r\n" .
            'To: "Bob" <sip:bob@biloxi.com>' . "\r\n" .
            'Contact: <sip:user@domain.com>' . "\r\n" .
            'Call-ID: 715' . "\r\n" .
            'CSeq: 7 METHOD' . "\r\n" .
            'Max-Forwards: 69' . "\r\n" .
            'Content-Length: 4' . "\r\n" .
            'Expires: 1800' . "\r\n" .
            'Min-Expires: 600' . "\r\n" .
            'Retry-After: 120' . "\r\n" .
            'Timestamp: 84' . "\r\n" .
            'Content-Type: mime/type' . "\r\n" .
            'Accept-Encoding: gzip' . "\r\n" .
            'Allow: gzip' . "\r\n" .
            'Allow-Events: ring' . "\r\n" .
            'Content-Encoding: gzip' . "\r\n" .
            'In-Reply-To: 8080@atlanta.bell-tel.com' . "\r\n" .
            'Require: 100rel' . "\r\n" .
            'Supported: replaces' . "\r\n" .
            'Unsupported: 101rel' . "\r\n" .
            'Proxy-Require: sec-agree' . "\r\n" .
            'Accept: mime/type' . "\r\n" .
            'Accept-Language: en' . "\r\n" .
            'Call-Info: <http://www.example.com/alice/photo.jpg>;purpose=icon' . "\r\n" .
            'Content-Language: es' . "\r\n" .
            'Reply-To: "Bob" <sip:bob@biloxi.com>' . "\r\n" .
            'Alert-Info: <file://external.ring.pcm>' . "\r\n" .
            'Authentication-Info: nextnonce="47364c23432d2e131a5fb210812c"' . "\r\n" .
            'Authorization: Digest realm="atlanta.example.com",opaque="",username="bob",uri="sips:ss2.biloxi.example.com",response="dfe56131d1958046689d83306477ecc",cnonce="ea9c8e88df84f1cec4341ae6cbe5a359",qop=auth,nc=00000042' . "\r\n" .
            'Date: Thu, 21 Feb 2002 13:02:03 GMT' . "\r\n" .
            'Error-Info: <sip:screen-failure-term-ann@annoucement.example.com>' . "\r\n" .
            'Proxy-Authenticate: Digest realm="atlanta.example.com",algorithm=MD5,nonce="f84f1cec41e6cbe5aea9c8e88d359",opaque="",stale=FALSE,qop="auth"' . "\r\n" .
            'Proxy-Authorization: Digest realm="atlanta.example.com",opaque="",username="alice",uri="sip:bob@biloxi.example.com",response="42ce3cef44b22f50c6a6071bc8",cnonce="wf84f1ceczx41ae6cbe5aea9c8e88d359"' . "\r\n" .
            'Record-Route: <sip:ss2.biloxi.example.com;lr>' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Organization: RTCKit' . "\r\n" .
            'Priority: urgent' . "\r\n" .
            'Route: <sip:ss1.atlanta.example.com;lr>' . "\r\n" .
            'Subject: Hello' . "\r\n" .
            'User-Agent: RTCKit\\SIP' . "\r\n" .
            'Warning: 301 isi.edu "Incompatible network address type \'E.164\'"' . "\r\n" .
            'WWW-Authenticate: Digest realm="atlanta.example.com",algorithm=MD5,nonce="84f1c1ae6cbe5ua9c8e88dfa3ecm3459",opaque="",stale=FALSE,qop="auth"' . "\r\n" .
            'X-Custom-Header: Something truly important' . "\r\n\r\n",
            $rendered
        );
    }
}
