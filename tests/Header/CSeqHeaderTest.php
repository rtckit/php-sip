<?php

declare(strict_types = 1);

namespace RTCKit\SIP\Header;

use RTCKit\SIP\Exception\InvalidDuplicateHeader;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderValue;
use RTCKit\SIP\Exception\InvalidScalarValue;
use RTCKit\SIP\Header\CSeqHeader;

use PHPUnit\Framework\TestCase;

class CSeqHeaderTest extends TestCase
{
    public function testShouldParseWellFormedValue()
    {
        $cseq = CSeqHeader::parse(['7 METHOD']);

        $this->assertNotNull($cseq);
        $this->assertInstanceOf(CSeqHeader::class, $cseq);
        $this->assertEquals(7, $cseq->sequence);
        $this->assertEquals('METHOD', $cseq->method);
    }

    public function testShouldNotParseMultiValue()
    {
        $this->expectException(InvalidDuplicateHeader::class);
        CSeqHeader::parse([
            '7 METHOD',
            '9 INVITE',
        ]);
    }

    public function testShouldNotParseNondelimitedComponents()
    {
        $this->expectException(InvalidHeaderLineException::class);
        CSeqHeader::parse(['7METHOD']);
    }

    public function testShouldNotParseNegativeSequence()
    {
        $this->expectException(InvalidScalarValue::class);
        CSeqHeader::parse(['-7 METHOD']);
    }

    public function testShouldNotParseOutOfBoundsSequence()
    {
        $this->expectException(InvalidScalarValue::class);
        CSeqHeader::parse(['42949672950 METHOD']);
    }

    public function testShouldNotParseMissingMethod()
    {
        $this->expectException(InvalidHeaderLineException::class);
        CSeqHeader::parse(['7  ']);
    }

    public function testShouldRenderWellFormedValue()
    {
        $cseq = new CSeqHeader;
        $cseq->sequence = 7;
        $cseq->method = 'METHOD';

        $rendered = $cseq->render('CSeq');

        $this->assertNotNull($rendered);
        $this->assertIsString($rendered);
        $this->assertEquals("CSeq: 7 METHOD\r\n", $rendered);
    }

    public function testShouldNotRenderMissingSequence()
    {
        $cseq = new CSeqHeader;
        $cseq->method = 'METHOD';

        $this->expectException(InvalidHeaderValue::class);
        $cseq->render('CSeq');
    }

    public function testShouldNotRenderMissingMethod()
    {
        $cseq = new CSeqHeader;
        $cseq->sequence = 7;

        $this->expectException(InvalidHeaderValue::class);
        $cseq->render('CSeq');
    }
}
