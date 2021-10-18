<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Auth\Digest\ResponseParams;
use RTCKit\SIP\Exception\AuthException;

use PHPUnit\Framework\TestCase;

/**
 * Digest response parameter tests
 *
 * https://datatracker.ietf.org/doc/html/draft-smith-sipping-auth-examples-01
 */
class ResponseParamsTest extends TestCase
{
    /**
     * https://datatracker.ietf.org/doc/html/draft-smith-sipping-auth-examples-01#section-3.1
     */
    public function testAlgorithmQopNotPresent()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->response = $params->hash('INVITE', 'zanzibar');

        $this->assertEquals('bf57e4e0d0bffc0fbaedce64d59add5e', $params->response);
    }

    /**
     * https://datatracker.ietf.org/doc/html/draft-smith-sipping-auth-examples-01#section-3.2
     */
    public function testAuthAlgorithmUnspecified()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->qop = "auth";
        $params->uri = "sip:bob@biloxi.com";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->response = $params->hash('INVITE', 'zanzibar');

        $this->assertEquals('89eb0059246c02b2f6ee02c7961d5ea3', $params->response);
    }

    /**
     * https://datatracker.ietf.org/doc/html/draft-smith-sipping-auth-examples-01#section-3.3
     */
    public function testAuthMD5()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->qop = "auth";
        $params->algorithm = "MD5";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->response = $params->hash('INVITE', 'zanzibar');

        $this->assertEquals('89eb0059246c02b2f6ee02c7961d5ea3', $params->response);
    }

    /**
     * https://datatracker.ietf.org/doc/html/draft-smith-sipping-auth-examples-01#section-3.4
     */
    public function testAuthMD5Sess()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->qop = "auth";
        $params->algorithm = "MD5-sess";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->response = $params->hash('INVITE', 'zanzibar');

        $this->assertEquals('e4e4ea61d186d07a92c9e1f6919902e9', $params->response);
    }

    /**
     * https://datatracker.ietf.org/doc/html/draft-smith-sipping-auth-examples-01#section-3.5
     */
    public function testAuthIntMD5()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->qop = "auth-int";
        $params->algorithm = "MD5";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";

        $body =
            'v=0' . "\r\n" .
            'o=bob 2890844526 2890844526 IN IP4 media.biloxi.com' . "\r\n" .
            's=-' . "\r\n" .
            'c=IN IP4 media.biloxi.com' . "\r\n" .
            't=0 0' . "\r\n" .
            'm=audio 49170 RTP/AVP 0' . "\r\n" .
            'a=rtpmap:0 PCMU/8000' . "\r\n" .
            'm=video 51372 RTP/AVP 31' . "\r\n" .
            'a=rtpmap:31 H261/90000' . "\r\n" .
            'm=video 53000 RTP/AVP 32' . "\r\n" .
            'a=rtpmap:32 MPV/90000' . "\r\n";

        $params->response = $params->hash('INVITE', 'zanzibar', null, $body);

        $this->assertEquals('bdbeebb2da6adb6bca02599c2239e192', $params->response);
    }

    /**
     * https://datatracker.ietf.org/doc/html/draft-smith-sipping-auth-examples-01#section-3.6
     */
    public function testAuthIntMD5Sess()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->qop = "auth-int";
        $params->algorithm = "MD5-sess";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";

        $body =
            'v=0' . "\r\n" .
            'o=bob 2890844526 2890844526 IN IP4 media.biloxi.com' . "\r\n" .
            's=-' . "\r\n" .
            'c=IN IP4 media.biloxi.com' . "\r\n" .
            't=0 0' . "\r\n" .
            'm=audio 49170 RTP/AVP 0' . "\r\n" .
            'a=rtpmap:0 PCMU/8000' . "\r\n" .
            'm=video 51372 RTP/AVP 31' . "\r\n" .
            'a=rtpmap:31 H261/90000' . "\r\n" .
            'm=video 53000 RTP/AVP 32' . "\r\n" .
            'a=rtpmap:32 MPV/90000' . "\r\n";

        $params->response = $params->hash('INVITE', 'zanzibar', null, $body);

        $this->assertEquals('91984da2d8663716e91554859c22ca70', $params->response);
    }

    /**
     * https://datatracker.ietf.org/doc/html/rfc7616#section-3.9.1
     */
    public function testSHA256MD5()
    {
        $params = new ResponseParams;
        $params->username = "Mufasa";
        $params->realm = "http-auth@example.org";
        $params->nonce = "7ypf/xlj9XXwfDPEoM4URrv/xwf94BcCAzFZH4GiTo0v";
        $params->uri = "/dir/index.html";
        $params->qop = "auth";
        $params->algorithm = "MD5";
        $params->nc = "00000001";
        $params->cnonce = "f2/wE4q74E6zIJEtWaHKaf5wv/H5QzzpXusqGemxURZJ";
        $params->opaque = "FQhe/qaU925kfnzjCev0ciny7QMkPqMAFRtzCUYo5tdS";
        $params->response = $params->hash('GET', 'Circle of Life');

        $this->assertEquals('8ca523f5e9506fed4657c9700eebdbec', $params->response);

        $params->algorithm = "SHA-256";
        $params->response = $params->hash('GET', 'Circle of Life');

        $this->assertEquals('753927fa0e85d155564e2e272a28d1802ca10daf4496794697cf8db5856cb6c1', $params->response);
    }

    public function testA1Hash()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->qop = "auth";
        $params->uri = "sip:bob@biloxi.com";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->response = $params->hash('INVITE', null, '12af60467a33e8518da5c68bbff12b11');

        $this->assertEquals('89eb0059246c02b2f6ee02c7961d5ea3', $params->response);
    }

    public function testAuthIntBodyNotPresent()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->qop = "auth-int";
        $params->response = $params->hash('INVITE', 'zanzibar');

        $this->assertEquals('2d6fc6e788367208f746582b18a69618', $params->response);
    }

    public function testFailUnknownQoP()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->qop = "auth-none";

        $this->expectException(AuthException::class);
        $params->response = $params->hash('INVITE', 'zanzibar');
    }

    public function testFailUnknownAlgo()
    {
        $params = new ResponseParams;
        $params->username = "bob";
        $params->realm = "biloxi.com";
        $params->nonce = "dcd98b7102dd2f0e8b11d0f600bfb0c093";
        $params->uri = "sip:bob@biloxi.com";
        $params->nc = "00000001";
        $params->cnonce = "0a4f113b";
        $params->opaque = "5ccc069c403ebaf9f0171e9517f40e41";
        $params->algorithm = "BFRX*/8";

        $this->expectException(AuthException::class);
        $params->response = $params->hash('INVITE', 'zanzibar');
    }
}
