<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidURIException;

use PHPUnit\Framework\TestCase;

/**
 * SIP URI Tests
 */
class URITest extends TestCase
{
    public function testShouldParseWellFormedURIs()
    {
        $uri = URI::parse('sip:+15551001:secret@example.com:5080;transport=udp;maddr=255.255.255.254;ttl=3600;user=phone;method=REGISTER;lr=lr;security=on?to=alice%40atlanta.com');
        $this->assertEquals('sip', $uri->scheme);
        $this->assertEquals('+15551001', $uri->user);
        $this->assertEquals('secret', $uri->password);
        $this->assertEquals('example.com', $uri->host);
        $this->assertEquals(5080, $uri->port);
        $this->assertEquals('udp', $uri->transport);
        $this->assertEquals('255.255.255.254', $uri->maddr);
        $this->assertEquals(3600, $uri->ttl);
        $this->assertEquals('register', $uri->method);
        $this->assertEquals('lr', $uri->lr);
        $this->assertEquals('phone', $uri->userParam);
        $this->assertEquals('on', $uri->params['security']);
        $this->assertEquals('alice@atlanta.com', $uri->headers['to']);
    }

    /**
     * Tests an edge case outlined in the RFC:
     * https://datatracker.ietf.org/doc/html/rfc3261#section-19.1.3
     */
    public function testOpaqueUserFieldValue()
    {
        $uri = URI::parse('sip:alice;day=tuesday@atlanta.com');
        $this->assertEquals('sip', $uri->scheme);
        $this->assertEquals('alice;day=tuesday', $uri->user);
        $this->assertEquals('atlanta.com', $uri->host);
    }

    public function testShouldRenderWellFormedURIs()
    {
        $uri = new URI;
        $uri->scheme = 'sip';
        $uri->user = '+15551001';
        $uri->password = 'secret';
        $uri->host = 'example.com';
        $uri->port = 5080;
        $uri->transport = 'udp';
        $uri->maddr = '255.255.255.254';
        $uri->ttl = 3600;
        $uri->method = 'REGISTER';
        $uri->lr = 'lr';
        $uri->userParam = 'phone';
        $uri->params['security'] = 'on';
        $uri->headers['to'] = 'alice@atlanta.com';

        $this->assertEquals(
            'sip:+15551001:secret@example.com:5080;transport=udp;maddr=255.255.255.254;ttl=3600;user=phone;method=REGISTER;lr=lr;security=on?to=alice%40atlanta.com',
            $uri->render()
        );
    }

    /**
     * Equivalence examples from the RFC:
     * https://datatracker.ietf.org/doc/html/rfc3261#page-155
     */
    public function testEquivalenceRFCExamples()
    {
        $uri0 = URI::parse('sip:%61lice@atlanta.com;transport=TCP');
        $uri1 = URI::parse('sip:alice@AtLanTa.CoM;Transport=tcp');
        $this->assertTrue($uri0->isEquivalent($uri1));
        $this->assertTrue($uri1->isEquivalent($uri0));

        $uri2 = URI::parse('sip:carol@chicago.com');
        $uri3 = URI::parse('sip:carol@chicago.com;newparam=5');
        $uri4 = URI::parse('sip:carol@chicago.com;security=on');
        $this->assertTrue($uri2->isEquivalent($uri3));
        $this->assertTrue($uri2->isEquivalent($uri4));
        $this->assertTrue($uri3->isEquivalent($uri2));
        $this->assertTrue($uri3->isEquivalent($uri4));
        $this->assertTrue($uri4->isEquivalent($uri2));
        $this->assertTrue($uri4->isEquivalent($uri3));

        $uri5 = URI::parse('sip:biloxi.com;transport=tcp;method=REGISTER?to=sip:bob%40biloxi.com');
        $uri6 = URI::parse('sip:biloxi.com;method=REGISTER;transport=tcp?to=sip:bob%40biloxi.com');
        $this->assertTrue($uri5->isEquivalent($uri6));
        $this->assertTrue($uri6->isEquivalent($uri5));

        $uri7 = URI::parse('sip:alice@atlanta.com?subject=project%20x&priority=urgent');
        $uri8 = URI::parse('sip:alice@atlanta.com?priority=urgent&subject=project%20x');
        $this->assertTrue($uri7->isEquivalent($uri8));
        $this->assertTrue($uri8->isEquivalent($uri7));

        /* different usernames */
        $uri9 = URI::parse('SIP:ALICE@AtLanTa.CoM;Transport=udp');
        $uri10 = URI::parse('sip:alice@AtLanTa.CoM;Transport=UDP');
        $this->assertFalse($uri9->isEquivalent($uri10));
        $this->assertFalse($uri10->isEquivalent($uri9));

        /* can resolve to different ports */
        $uri11 = URI::parse('sip:bob@biloxi.com');
        $uri12 = URI::parse('sip:bob@biloxi.com:5060');
        $this->assertFalse($uri11->isEquivalent($uri12));
        $this->assertFalse($uri12->isEquivalent($uri11));

        /* can resolve to different transports */
        $uri13 = URI::parse('sip:bob@biloxi.com');
        $uri14 = URI::parse('sip:bob@biloxi.com;transport=udp');
        $this->assertFalse($uri13->isEquivalent($uri14));
        $this->assertFalse($uri14->isEquivalent($uri13));

        /* can resolve to different port and transports */
        $uri15 = URI::parse('sip:bob@biloxi.com');
        $uri16 = URI::parse('sip:bob@biloxi.com:6000;transport=udp');
        $this->assertFalse($uri15->isEquivalent($uri16));
        $this->assertFalse($uri16->isEquivalent($uri15));

        /* different header component */
        $uri17 = URI::parse('sip:carol@chicago.com');
        $uri18 = URI::parse('sip:carol@chicago.com?Subject=next%20meeting');
        $this->assertFalse($uri17->isEquivalent($uri18));
        $this->assertFalse($uri18->isEquivalent($uri17));

        /* even though that's what phone21.boxesbybob.com resolves to */
        $uri19 = URI::parse('sip:bob@phone21.boxesbybob.com');
        $uri20 = URI::parse('sip:bob@192.0.2.4');
        $this->assertFalse($uri19->isEquivalent($uri20));
        $this->assertFalse($uri20->isEquivalent($uri19));

        /* equality is not transitive */
        $uri21 = URI::parse('sip:carol@chicago.com');
        $uri22 = URI::parse('sip:carol@chicago.com;security=on');
        $uri23 = URI::parse('sip:carol@chicago.com;security=off');
        $this->assertTrue($uri21->isEquivalent($uri22));
        $this->assertTrue($uri22->isEquivalent($uri21));
        $this->assertTrue($uri21->isEquivalent($uri23));
        $this->assertTrue($uri23->isEquivalent($uri21));
        $this->assertFalse($uri22->isEquivalent($uri23));
        $this->assertFalse($uri23->isEquivalent($uri22));
    }

    public function testNonEquivalence()
    {
        $uri0 = URI::parse('sip:carol@chicago.com');
        $uri1 = URI::parse('sips:carol@chicago.com');
        $this->assertFalse($uri0->isEquivalent($uri1));
        $this->assertFalse($uri1->isEquivalent($uri0));

        $uri2 = URI::parse('sip:carol@chicago.com');
        $uri3 = URI::parse('sip:carol:secret@chicago.com');
        $this->assertFalse($uri2->isEquivalent($uri3));
        $this->assertFalse($uri3->isEquivalent($uri2));

        $uri4 = URI::parse('sip:carol@chicago.com');
        $uri5 = URI::parse('sip:carol@chicago.com;maddr=255.255.255.254');
        $this->assertFalse($uri4->isEquivalent($uri5));
        $this->assertFalse($uri5->isEquivalent($uri4));

        $uri6 = URI::parse('sip:carol@chicago.com');
        $uri7 = URI::parse('sip:carol@chicago.com;ttl=60');
        $this->assertFalse($uri6->isEquivalent($uri7));
        $this->assertFalse($uri7->isEquivalent($uri6));

        $uri8 = URI::parse('sip:carol@chicago.com');
        $uri9 = URI::parse('sip:carol@chicago.com;user=phone');
        $this->assertFalse($uri8->isEquivalent($uri9));
        $this->assertFalse($uri9->isEquivalent($uri8));

        $uri10 = URI::parse('sip:carol@chicago.com');
        $uri11 = URI::parse('sip:carol@chicago.com;method=REGISTER');
        $this->assertFalse($uri10->isEquivalent($uri11));
        $this->assertFalse($uri11->isEquivalent($uri10));
    }

    public function testUnescapeToSkipPlusSign()
    {
        $this->assertEquals('+123', URI::unescape('+123'));
    }

    public function testEscapeToSkipPlusSign()
    {
        $this->assertEquals('+123', URI::escape('+123'));
    }

    public function testShouldParseIPv6Hosts()
    {
        $uri0 = URI::parse('sip:123012345678901@[2409:8805:84e3:3603::1]:1388');
        $this->assertTrue($uri0->ipv6);
        $this->assertEquals('2409:8805:84e3:3603::1', $uri0->host);
        $this->assertEquals(1388, $uri0->port);
        $this->assertEquals('sip:123012345678901@[2409:8805:84e3:3603::1]:1388', $uri0->render());

        $uri1 = URI::parse('sip:123012345678901@[2409:8805:84e3:3603::1]');
        $this->assertTrue($uri1->ipv6);
        $this->assertEquals('2409:8805:84e3:3603::1', $uri1->host);
        $this->assertTrue(!isset($uri1->port));
        $this->assertEquals('sip:123012345678901@[2409:8805:84e3:3603::1]', $uri1->render());
    }

    public function testShouldNotParseInvalidURI()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('Not an URI');
    }

    public function testShouldNotParseEmptyParameterNames()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:atlanta.com;;security=on');
    }

    public function testShouldNotParseInvalidMAddr()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:atlanta.com;maddr=example.com');
    }

    public function testShouldNotParseInvalidTTL()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:atlanta.com;ttl=forever');
    }

    public function testShouldNotParseDuplicateParameters()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:atlanta.com;security=on;security=indeed');
    }

    public function testShouldNotParseInvalidPort()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:atlanta.com:sipport');
    }

    public function testShouldNotParseInvalidPort2()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:atlanta.com:75060');
    }

    public function testShouldNotParseInvalidHost()
    {
        $this->expectException(InvalidURIException::class);
        var_dump(URI::parse('sip:atlanta+com'));
    }

    public function testShouldNotRenderMissingScheme()
    {
        $this->expectException(InvalidURIException::class);
        $uri = new URI;
        $uri->host = 'example.com';
        $uri->render();
    }

    public function testShouldNotRenderMissingHost()
    {
        $this->expectException(InvalidURIException::class);
        $uri = new URI;
        $uri->scheme = 'sips';
        $uri->render();
    }

    public function testShouldNotCompareInvalidURIs()
    {
        $this->expectException(InvalidURIException::class);
        $uri0 = new URI;
        $uri1 = new URI;
        $uri0->isEquivalent($uri1);
    }

    public function testShouldNotParseOpenEndedIPv6()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:123012345678901@[2409:8805:84e3:3603::1');
    }

    public function testShouldNotParseUnescapedIPv6()
    {
        $this->expectException(InvalidURIException::class);
        URI::parse('sip:123012345678901@2409:8805:84e3:3603::1');
    }
}
