<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use RTCKit\SIP\Message;
use RTCKit\SIP\Request;

/**
 * https://tools.ietf.org/html/rfc4475#section-3.1.1.2
 * 3.1.1.2.  Wide Range of Valid Characters
 */
class S31102Test extends RFC4475Case
{
    public function testShouldParseProperly()
    {
        $pdu = $this->loadFixture('intmeth');

        $msg = Message::parse($pdu);

        $this->assertInstanceOf(Request::class, $msg);

        /* The Method contains non-alpha characters from token */
        $this->assertEquals('!interesting-Method0123456789_*+`.%indeed\'~', $msg->method);

        /* The Request-URI contains unusual, but legal, characters */
        $this->assertEquals('sip', $msg->uri->scheme);
        $this->assertEquals("1_unusual.URI~(to-be!sure)&isn't+it$/crazy?,/;;*", $msg->uri->user);
        $this->assertEquals("&it+has=1,weird!*pas\$wo~d_too.(doesn't-it)", $msg->uri->password);
        $this->assertEquals('example.com', $msg->uri->host);

        /* A branch parameter contains all non-alphanum characters from token */
        $this->assertEquals('z9hG4bK-.!%66*_+`\'~', $msg->via->values[0]->branch);

        /* The To header field value's quoted string contains quoted-pair expansions, including a quoted NULL character */
        $this->assertEquals('BEL:\\' . "\x07" . ' NUL:\\' . "\x00" . ' DEL:\\' . "\x7f", $msg->to->name);

        /* The name part of name-addr in the From header field value contains
           multiple tokens (instead of a quoted string) with all non-alphanum
           characters from the token production rule. */
        $this->assertEquals('token1~` token2\'+_ token3*%!.-', $msg->from->name);

        /* That value also has an unknown header parameter whose name contains the non-alphanum
           token characters and whose value is a non-ascii range UTF-8 encoded string. */
        $this->assertNotNull($msg->from->params['fromParam\'\'~+*_!.-%']);
        $this->assertEquals(
            "\"\xD1\x80\xD0\xB0\xD0\xB1\xD0\xBE\xD1\x82\xD0\xB0\xD1\x8E\xD1\x89\xD0\xB8\xD0\xB9\"",
            $msg->from->params['fromParam\'\'~+*_!.-%']
        );

        /* The tag parameter on this value contains the non-alphanum token characters. */
        $this->assertEquals('_token~1\'+`*%!-.', $msg->from->tag);

        /* The Call-ID header field value contains the non-alphanum characters from word */
        $this->assertEquals('intmeth.word%ZK-!.*_+\'@word`~)(><:\\/"][?}{', $msg->callId->value);

        /* There is an unknown header field (matching extension-header) with
           non-alphanum token characters in its name and a UTF8-NONASCII value. */
        $this->assertEquals(
            "\xEF\xBB\xBF\xE5\xA4\xA7\xE5\x81\x9C\xE9\x9B\xBB",
            $msg->extraHeaders['extensionheader-!.%*+_`\'~']->values[0]
        );
    }
}
