<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\URI;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderParameterException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Header\ContactHeader;
use RTCKit\SIP\Header\ContactValue;

use PHPUnit\Framework\TestCase;

class ContactHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValues()
    {
        $contact = ContactHeader::parse([
            '  "Bob \" Quote" <sips:bob@client.biloxi.example.com>;expires=3600, <sips:bob@atlanta.example.com>',
            'sips:bob@office.biloxi.example.com;q=0.1',
            'Bob <sips:bob@biloxi.example.com>;expires=4294967295;custom=parameter',
            'sips:bob@offshore.biloxi.example.com',
        ]);

        $this->assertNotNull($contact);
        $this->assertInstanceOf(ContactHeader::class, $contact);
        $this->assertCount(5, $contact->values);
        $this->assertEquals('sips:bob@client.biloxi.example.com', $contact->values[0]->uri->render());
        $this->assertEquals('Bob \" Quote', $contact->values[0]->name);
        $this->assertEquals(3600, $contact->values[0]->expires);
        $this->assertEquals('sips:bob@atlanta.example.com', $contact->values[1]->uri->render());
        $this->assertEquals('sips:bob@office.biloxi.example.com', $contact->values[2]->uri->render());
        $this->assertEquals(0.1, $contact->values[2]->q);
        $this->assertEquals('sips:bob@biloxi.example.com', $contact->values[3]->uri->render());
        $this->assertEquals('Bob', $contact->values[3]->name);
        $this->assertEquals(4294967295, $contact->values[3]->expires);
        $this->assertEquals('parameter', $contact->values[3]->params['custom']);
        $this->assertEquals('sips:bob@offshore.biloxi.example.com', $contact->values[4]->uri->render());
        $this->assertFalse($contact->wildcard);
    }

    public function testShouldParseWildcardValue()
    {
        $contact = ContactHeader::parse(['*']);

        $this->assertNotNull($contact);
        $this->assertInstanceOf(ContactHeader::class, $contact);
        $this->assertTrue($contact->wildcard);
    }

    public function testShouldNotParseBeginingEndingEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        ContactHeader::parse(['<sips:bob@offshore.biloxi.example.com']);
    }

    public function testShouldNotParseUnmatchedMiddleEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        ContactHeader::parse(['<sips:<bob@offshore.biloxi.example.com>']);
    }

    public function testShouldNotParseUnmatchedEndingEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        ContactHeader::parse(['sips:bob@offshore.biloxi.example.com>']);
    }

    public function testShouldNotParseUnmatchedParameterEscapedURI()
    {
        $this->expectException(InvalidHeaderLineException::class);
        ContactHeader::parse(['<sips:bob@offshore.biloxi.example.com>;>expires=1800']);
    }

    public function testShouldNotparseMalformedWildcardValue()
    {
        $this->expectException(InvalidHeaderLineException::class);
        $contact = ContactHeader::parse([
            '*',
            'sips:bob@offshore.biloxi.example.com',
        ]);
    }

    public function testShouldNotParseEmptyParameterNames()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ContactHeader::parse(['sips:bob@offshore.biloxi.example.com;q=0.7;;expires=1800']);
    }

    public function testShouldNotParseEmptyParameterNames2()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ContactHeader::parse(['sips:bob@offshore.biloxi.example.com; =1800']);
    }

    public function testShouldNotParseMultipleQParameters()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ContactHeader::parse(['sips:bob@offshore.biloxi.example.com;q=0.1;q=0.2']);
    }

    public function testShouldNotParseMultipleExpiresParameters()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ContactHeader::parse(['sips:bob@offshore.biloxi.example.com;expires=1800;expires=3600']);
    }

    public function testShouldNotParseMultipleCustomParameters()
    {
        $this->expectException(InvalidHeaderParameterException::class);
        ContactHeader::parse(['sips:bob@offshore.biloxi.example.com;custom=value;custom=again']);
    }

    public function testShouldNotParseUnmatchedQuotes()
    {
        $this->expectException(InvalidHeaderLineException::class);
        ContactHeader::parse(['"Bogus Bob <sips:bob@offshore.biloxi.example.com>']);
    }

    public function testShouldRenderWildcardValue()
    {
        $contact = new ContactHeader;
        $contact->wildcard = true;

        $rendered = $contact->render('Contact');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("Contact: *\r\n", $rendered);
    }

    public function testShouldRenderWellFormedValues()
    {
        $contact = new ContactHeader;
        $contact->values[0] = new ContactValue;
        $contact->values[0]->name = 'Bob';
        $contact->values[0]->uri = URI::parse('sip:bob@offshore.biloxi.example.com');
        $contact->values[0]->q = 0.7;
        $contact->values[0]->expires = 600;
        $contact->values[1] = new ContactValue;
        $contact->values[1]->uri = URI::parse('sip:bob@office.biloxi.example.com');
        $contact->values[1]->params['unknown'] = 'parameter';

        $rendered = $contact->render('Contact');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals(
            'Contact: "Bob" <sip:bob@offshore.biloxi.example.com>;q=0.7;expires=600, <sip:bob@office.biloxi.example.com>;unknown=parameter' . "\r\n",
            $rendered
        );
    }

    public function testShouldNotRenderMissingValues()
    {
        $contact = new ContactHeader;

        $this->expectException(InvalidHeaderValueException::class);
        $contact->render('Contact');
    }

    public function testShouldNotRenderMissingNameAddr()
    {
        $contact = new ContactHeader;
        $contact->values[0] = new ContactValue;
        $contact->values[0]->name = 'Bob';
        $contact->values[0]->q = 0.7;
        $contact->values[0]->expires = 600;

        $this->expectException(InvalidHeaderValueException::class);
        $contact->render('Contact');
    }
}
