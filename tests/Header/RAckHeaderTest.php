<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidDuplicateHeaderException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderValueException;
use RTCKit\SIP\Exception\InvalidScalarValueException;
use RTCKit\SIP\Header\RAckHeader;

use PHPUnit\Framework\TestCase;

class RAckHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $rack = RAckHeader::parse(['42 7 METHOD']);

        $this->assertNotNull($rack);
        $this->assertInstanceOf(RAckHeader::class, $rack);
        $this->assertEquals(42, $rack->rSequence);
        $this->assertEquals(7, $rack->cSequence);
        $this->assertEquals('METHOD', $rack->method);
    }

    public function testShouldNotParseMultiValue()
    {
        $this->expectException(InvalidDuplicateHeaderException::class);
        RAckHeader::parse([
            '63 7 METHOD',
            '41 9 INVITE',
        ]);
    }

    public function testShouldNotParseNondelimitedComponents()
    {
        $this->expectException(InvalidHeaderLineException::class);
        RAckHeader::parse(['427METHOD']);
    }

    public function testShouldNotParseNondelimitedComponents2()
    {
        $this->expectException(InvalidHeaderLineException::class);
        RAckHeader::parse(['42 7METHOD']);
    }

    public function testShouldNotParseNegativeProvisionalSequence()
    {
        $this->expectException(InvalidScalarValueException::class);
        RAckHeader::parse(['-7 1 METHOD']);
    }

    public function testShouldNotParseNegativeSequence()
    {
        $this->expectException(InvalidScalarValueException::class);
        RAckHeader::parse(['7 -1 METHOD']);
    }

    public function testShouldNotParseOutOfBoundsProvisionalSequence()
    {
        $this->expectException(InvalidScalarValueException::class);
        RAckHeader::parse(['42949672950 1 METHOD']);
    }

    public function testShouldNotParseOutOfBoundsSequence()
    {
        $this->expectException(InvalidScalarValueException::class);
        RAckHeader::parse(['1 42949672950 METHOD']);
    }

    public function testShouldNotParseMissingMethod()
    {
        $this->expectException(InvalidHeaderLineException::class);
        RAckHeader::parse(['7  ']);
    }

    public function testShouldNotParseMissingMethod2()
    {
        $this->expectException(InvalidHeaderLineException::class);
        RAckHeader::parse(['42 1 ']);
    }

    public function testShouldRenderWellFormedValue()
    {
        $rack = new RAckHeader;
        $rack->rSequence = 42;
        $rack->cSequence = 7;
        $rack->method = 'METHOD';

        $rendered = $rack->render('RAck');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("RAck: 42 7 METHOD\r\n", $rendered);
    }

    public function testShouldNotRenderMissingProvisionalSequence()
    {
        $rack = new RAckHeader;
        $rack->method = 'METHOD';

        $this->expectException(InvalidHeaderValueException::class);
        $rack->render('RAck');
    }

    public function testShouldNotRenderMissingProvisionalSequence2()
    {
        $rack = new RAckHeader;
        $rack->cSequence = 7;
        $rack->method = 'METHOD';

        $this->expectException(InvalidHeaderValueException::class);
        $rack->render('RAck');
    }

    public function testShouldNotRenderMissingSequence()
    {
        $rack = new RAckHeader;
        $rack->method = 'METHOD';

        $this->expectException(InvalidHeaderValueException::class);
        $rack->render('RAck');
    }

    public function testShouldNotRenderMissingSequence2()
    {
        $rack = new RAckHeader;
        $rack->rSequence = 42;
        $rack->method = 'METHOD';

        $this->expectException(InvalidHeaderValueException::class);
        $rack->render('RAck');
    }

    public function testShouldNotRenderMissingMethod()
    {
        $rack = new RAckHeader;
        $rack->rSequence = 42;
        $rack->cSequence = 7;

        $this->expectException(InvalidHeaderValueException::class);
        $rack->render('RAck');
    }
}
