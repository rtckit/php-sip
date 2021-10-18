<?php
/**
 * RTCKit\SIP\Request Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidMessageStartLineException;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;
use RTCKit\SIP\Exception\InvalidRequestMethodException;
use RTCKit\SIP\Exception\InvalidRequestURIException;

/**
 * SIP Request class
 */
class Request extends Message
{
    /** @var string Request method */
    public string $method;

    /** @var URI Request URI */
    public URI $uri;

    /**
     * SIP Request constructor
     *
     * @param ?string $startLine Raw message start line
     * @throws InvalidMessageStartLineException
     * @throws InvalidProtocolVersionException
     * @throws InvalidRequestURIException
     */
    public function __construct(?string $startLine = null)
    {
        if (is_null($startLine)) {
            return;
        }

        $rqstLine = explode(' ', $startLine);

        if (count($rqstLine) !== 3) {
            throw new InvalidMessageStartLineException('Malformed Request-Line: ' . $startLine, Response::BAD_REQUEST);
        }

        if ($rqstLine[1][0] === '<') {
            throw new InvalidRequestURIException('Cannot enclose <> request URIs', Response::BAD_REQUEST);
        }

        if ($rqstLine[2] !== Message::SIP_VERSION) {
            throw new InvalidProtocolVersionException('Unsupported SIP version: ' . $rqstLine[2], Response::VERSION_NOT_SUPPORTED);
        }

        $this->version = $rqstLine[2];
        $this->method = $rqstLine[0];
        $this->uri = URI::parse($rqstLine[1]);

        if (count($this->uri->headers)) {
            /*
             * https://datatracker.ietf.org/doc/html/rfc3261#page-152
             *
             * We interpret this that, as a UAS, we should ignore any headers in Request-URIs.
             *
             * Also, relevant to RFC 4475 3.1.2.11:
             * https://datatracker.ietf.org/doc/html/rfc4475#section-3.1.2.11
             */
            $this->uri->headers = [];
        }
    }

    /**
     * SIP Response Renderer
     *
     * @param bool $compact Whether to output compact headers or not
     * @throws InvalidRequestMethodException
     * @throws InvalidRequestURIException
     * @return string SIP response as text
     */
    public function render(bool $compact = false): string
    {
        if (!isset($this->method[0])) {
            throw new InvalidRequestMethodException('Missing request method');
        }

        if (!isset($this->uri, $this->uri->scheme, $this->uri->host)) {
            throw new InvalidRequestURIException('Missing/invalid request URI');
        }

        if (count($this->uri->headers)) {
            /*
             * https://datatracker.ietf.org/doc/html/rfc3261#page-152
             *
             * We interpret this that, as a UAC, we should never issue headers in Request-URIs.
             */
            throw new InvalidRequestURIException('Headers present in request URI');
        }

        $uriStr = $this->uri->render();
        $this->version ??= Message::SIP_VERSION;
        $this->body ??= '';
        $headers = $this->renderHeaders($compact);

        return "{$this->method} {$uriStr} {$this->version}\r\n{$headers}\r\n{$this->body}";
    }
}
