<?php
/**
* RTCKit\SIP\Request Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidMessageStartLineException;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;
use RTCKit\SIP\Exception\InvalidRequestMethod;
use RTCKit\SIP\Exception\InvalidRequestURI;

/**
* SIP Request class
*/
class Request extends Message
{
    /** @var string Request method */
    public string $method;

    /** @var string Request URI */
    public string $uri;

    /**
     * SIP Request constructor
     *
     * @param ?string $startLine Raw message start line
     * @throws InvalidMessageStartLineException
     * @throws InvalidProtocolVersionException
     * @throws InvalidRequestURI
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
            throw new InvalidRequestURI('Cannot enclose <> request URIs', Response::BAD_REQUEST);
        }

        if ($rqstLine[2] !== Message::SIP_VERSION) {
            throw new InvalidProtocolVersionException('Unsupported SIP version: ' . $rqstLine[2], Response::VERSION_NOT_SUPPORTED);
        }

        $this->version = $rqstLine[2];
        $this->method = $rqstLine[0];
        $this->uri = $rqstLine[1];
    }

    /**
     * SIP Response Renderer
     *
     * @param bool $compact Whether to output compact headers or not
     * @throws InvalidRequestMethod
     * @throws InvalidRequestURI
     * @return string SIP response as text
     */
    public function render(bool $compact = false): string
    {
        if (!isset($this->method[0])) {
            throw new InvalidRequestMethod('Missing request method');
        }

        if (!isset($this->uri[0])) {
            throw new InvalidRequestURI('Missing request URI');
        }

        $this->version ??= Message::SIP_VERSION;
        $this->body ??= '';
        $headers = $this->renderHeaders($compact);

        return "{$this->method} {$this->uri} {$this->version}\r\n{$headers}\r\n{$this->body}";
    }
}
