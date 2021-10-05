<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidMessageStartLineException;
use RTCKit\SIP\Exception\InvalidScalarValueException;

use PHPUnit\Framework\TestCase;

/**
 * Stream Parsing Tests
 */
class StreamParserTest extends TestCase
{
    /** @var array Paths to well constructed SIP stream files and their known message counts */
    public const STREAMS = [
        [
            'path' => __DIR__ . '/fixtures/stream/generic.txt',
            'messages' => 227,
        ],
        /* lioneagle/sipparser test material:
         * https://github.com/lioneagle/sipparser/blob/master/src/testdata/sip_msg.txt
         */
        [
            'path' => __DIR__ . '/fixtures/stream/lioneagle-sipparser.txt',
            'messages' => 26,
        ],
    ];

    public StreamParser $parser;

    protected function setUp(): void
    {
        $this->parser = new StreamParser;
    }

    public function testShouldParseVariousChunkSizes()
    {
        foreach (self::STREAMS as $stream) {
            $fp = fopen($stream['path'], 'r');

            $this->assertNotNull($fp);
            $this->assertNotNull($this->parser);

            for($i = 0; $i <= 17; $i++) {
                fseek($fp, 0);

                $chunkSize = pow(2, $i);
                $count = 0;

                while (!feof($fp)) {
                    $bytes = fread($fp, $chunkSize);
                    $ret = $this->parser->process($bytes, $messages);

                    if ($ret === StreamParser::SUCCESS) {
                        foreach ($messages as $message) {
                            /* Messages could be parsed, no exceptions to be thrown */
                            $this->assertInstanceOf(Message::class, $message);

                            /* Resulting message can be rendered, no exceptions to be thrown */
                            $this->assertIsString($message->render());

                            $count++;
                        }
                    }
                }

                $this->assertEquals($stream['messages'], $count);
            }

            fclose($fp);
        }
    }

    public function testShouldParseStreamedMessagesNoDifferentThanPDU()
    {
        $bytes = file_get_contents(__DIR__ . '/fixtures/rfc4475/wsinv.dat');

        if ($this->parser->process($bytes, $messages) === StreamParser::SUCCESS) {
            /* Expect one message and one message only */
            $this->assertCount(1, $messages);

            /* The streamed message to be parsed as if it was an individual PDU */
            $this->assertEquals(Message::parse($bytes), $messages[0]);
        }
    }

    public function testFailureWithoutContentLength()
    {
        $bytes =
            'INVITE sip:bob@biloxi.example.com SIP/2.0' . "\r\n" .
            'Via: SIP/2.0/UDP client.atlanta.example.com:5060;branch=z9hG4bK74bf9' . "\r\n" .
            'Max-Forwards: 70' . "\r\n" .
            'Route: <sip:ss1.atlanta.example.com;lr>' . "\r\n" .
            'From: Alice <sip:alice@atlanta.example.com>;tag=9fxced76sl' . "\r\n" .
            'To: Bob <sip:bob@biloxi.example.com>' . "\r\n" .
            'Call-ID: 2xTb9vxSit55XU7p8@atlanta.example.com' . "\r\n" .
            'CSeq: 1 INVITE' . "\r\n" .
            'Contact: <sip:alice@client.atlanta.example.com>' . "\r\n" .
            'Proxy-Authorization: Digest username="alice",' . "\r\n" .
            ' realm="atlanta.example.com",' . "\r\n" .
            ' nonce="aa9311cf5904ba7d8dc3a5ab253028fa", opaque="",' . "\r\n" .
            ' uri="sip:bob@biloxi.example.com",' . "\r\n" .
            ' response="59a46a91bf1646562a4d486c84b399db"' . "\r\n" .
            'Content-Type: application/sdp' . "\r\n" .
            "\r\n" .
            'o=alice 2890844526 2890844526 IN IP4 client.atlanta.example.com' . "\r\n" .
            's=-' . "\r\n" .
            'c=IN IP4 192.0.2.101' . "\r\n" .
            't=0 0' . "\r\n" .
            'm=audio 49172 RTP/AVP 0' . "\r\n" .
            'a=rtpmap:0 PCMU/8000' . "\r\n" .
            "\r\n" .
            'INVITE sip:bob@biloxi.example.com SIP/2.0' . "\r\n" .
            'Via: SIP/2.0/UDP ss1.atlanta.example.com:5060;branch=z9hG4bK2d4790.1' . "\r\n" .
            'Via: SIP/2.0/UDP client.atlanta.example.com:5060;branch=z9hG4bK74bf9' . "\r\n" .
            ';received=192.0.2.101' . "\r\n" .
            'Max-Forwards: 69' . "\r\n" .
            'Record-Route: <sip:ss1.atlanta.example.com;lr>' . "\r\n" .
            'From: Alice <sip:alice@atlanta.example.com>;tag=9fxced76sl' . "\r\n" .
            'To: Bob <sip:bob@biloxi.example.com>' . "\r\n" .
            'Call-ID: 2xTb9vxSit55XU7p8@atlanta.example.com' . "\r\n" .
            'CSeq: 1 INVITE' . "\r\n" .
            'Contact: <sip:alice@client.atlanta.example.com>' . "\r\n" .
            'Content-Type: application/sdp' . "\r\n" .
            'Content-Length: 151' . "\r\n" . /* Note the previous INVITE lacks the Content-Length header */
            '' . "\r\n" .
            'v=0' . "\r\n" .
            'o=alice 2890844526 2890844526 IN IP4 client.atlanta.example.com' . "\r\n" .
            's=-' . "\r\n" .
            'c=IN IP4 192.0.2.101' . "\r\n" .
            't=0 0' . "\r\n" .
            'm=audio 49172 RTP/AVP 0' . "\r\n" .
            'a=rtpmap:0 PCMU/8000';

        // Throws InvalidMessageStartLineException
        $this->expectException(InvalidMessageStartLineException::class);
        $this->parser->process($bytes, $messages);
    }

    public function testFailureWithNegativeContentLength()
    {
        $bytes =
            'INVITE sip:bob@biloxi.example.com SIP/2.0' . "\r\n" .
            'Via: SIP/2.0/UDP client.atlanta.example.com:5060;branch=z9hG4bK74bf9' . "\r\n" .
            'Max-Forwards: 70' . "\r\n" .
            'Route: <sip:ss1.atlanta.example.com;lr>' . "\r\n" .
            'From: Alice <sip:alice@atlanta.example.com>;tag=9fxced76sl' . "\r\n" .
            'To: Bob <sip:bob@biloxi.example.com>' . "\r\n" .
            'Call-ID: 2xTb9vxSit55XU7p8@atlanta.example.com' . "\r\n" .
            'CSeq: 1 INVITE' . "\r\n" .
            'Contact: <sip:alice@client.atlanta.example.com>' . "\r\n" .
            'Proxy-Authorization: Digest username="alice",' . "\r\n" .
            ' realm="atlanta.example.com",' . "\r\n" .
            ' nonce="aa9311cf5904ba7d8dc3a5ab253028fa", opaque="",' . "\r\n" .
            ' uri="sip:bob@biloxi.example.com",' . "\r\n" .
            ' response="59a46a91bf1646562a4d486c84b399db"' . "\r\n" .
            'Content-Type: application/sdp' . "\r\n" .
            'Content-Length: -255' . "\r\n" .
            "\r\n" .
            'o=alice 2890844526 2890844526 IN IP4 client.atlanta.example.com' . "\r\n" .
            's=-' . "\r\n" .
            'c=IN IP4 192.0.2.101' . "\r\n" .
            't=0 0' . "\r\n" .
            'm=audio 49172 RTP/AVP 0' . "\r\n" .
            'a=rtpmap:0 PCMU/8000' . "\r\n" .
            "\r\n";

        // Throws InvalidScalarValueException
        $this->expectException(InvalidScalarValueException::class);
        $this->parser->process($bytes, $messages);
    }
}
