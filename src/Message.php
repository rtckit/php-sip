<?php
/**
 * RTCKit\SIP\Message Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidBodyLengthException;
use RTCKit\SIP\Exception\InvalidCSeqValueException;
use RTCKit\SIP\Exception\InvalidHeaderLineException;
use RTCKit\SIP\Exception\InvalidHeaderSectionException;
use RTCKit\SIP\Exception\SIPException;
use RTCKit\SIP\Header\AuthenticateHeader;
use RTCKit\SIP\Header\AuthorizationHeader;
use RTCKit\SIP\Header\CallIdHeader;
use RTCKit\SIP\Header\ContactHeader;
use RTCKit\SIP\Header\CSeqHeader;
use RTCKit\SIP\Header\Header;
use RTCKit\SIP\Header\FromHeader;
use RTCKit\SIP\Header\MaxForwardsHeader;
use RTCKit\SIP\Header\MultiValueHeader;
use RTCKit\SIP\Header\MultiValueWithParamsHeader;
use RTCKit\SIP\Header\NameAddrHeader;
use RTCKit\SIP\Header\RAckHeader;
use RTCKit\SIP\Header\RSeqHeader;
use RTCKit\SIP\Header\ScalarHeader;
use RTCKit\SIP\Header\SingleValueWithParamsHeader;
use RTCKit\SIP\Header\ViaHeader;

/**
 * Base SIP message class
 */
class Message
{
    /* Protocol constants */
    public const SIP_VERSION = 'SIP/2.0';
    public const SIP_PROTOCOL_NAME = 'SIP';
    public const SIP_VERSION_NUMBER = '2.0';

    /* Compact header definitions */
    public const COMPACT_HEADERS = [
        'c' => 'content-type',
        'e' => 'content-encoding',
        'f' => 'from',
        'i' => 'call-id',
        'k' => 'supported',
        'l' => 'content-length',
        'm' => 'contact',
        'o' => 'event',
        'r' => 'refer-to',
        's' => 'subject',
        't' => 'to',
        'u' => 'allow-events',
        'v' => 'via',
    ];

    /** @var string Message SIP Version */
    public string $version;

    /* Via header field */
    public ViaHeader $via;

    /* From/To header field */
    public NameAddrHeader $from;
    public NameAddrHeader $to;

    /* Contact header field */
    public ContactHeader $contact;

    /* Call-ID header field */
    public CallIdHeader $callId;

    /* CSeq header field */
    public CSeqHeader $cSeq;

    /* Scalar header fields */
    public ScalarHeader $maxForwards;
    public ScalarHeader $contentLength;
    public ScalarHeader $expires;
    public ScalarHeader $minExpires;
    public ScalarHeader $retryAfter;
    public ScalarHeader $timestamp;

    /* Single value with parameters header fields */
    public SingleValueWithParamsHeader $contentType;
    public SingleValueWithParamsHeader $event;
    public SingleValueWithParamsHeader $subscriptionState;

    /* Multiple value header fields */
    public MultiValueHeader $acceptEncoding;
    public MultiValueHeader $allow;
    public MultiValueHeader $allowEvents;
    public MultiValueHeader $contentEncoding;
    public MultiValueHeader $inReplyTo;
    public MultiValueHeader $require;
    public MultiValueHeader $supported;
    public MultiValueHeader $unsupported;
    public MultiValueHeader $proxyRequire;

    /* Multiple value with parameters header fields */
    public MultiValueWithParamsHeader $accept;
    public MultiValueWithParamsHeader $acceptLanguage;
    public MultiValueWithParamsHeader $callInfo;
    public MultiValueWithParamsHeader $contentLanguage;

    /* Reply-To header field */
    public NameAddrHeader $replyTo;

    /* Authentication/Authorization header fields */
    public AuthenticateHeader $wwwAuthenticate;
    public AuthorizationHeader $authorization;
    public AuthenticateHeader $proxyAuthenticate;
    public AuthorizationHeader $proxyAuthorization;

    /* Generic common header fields */
    public Header $alertInfo;
    public Header $authenticationInfo;
    public Header $date;
    public Header $errorInfo;
    public Header $recordRoute;
    public Header $mimeVersion;
    public Header $organization;
    public Header $priority;
    public Header $route;
    public Header $subject;
    public Header $userAgent;
    public Header $warning;

    /* REFER header fields */
    public Header $referTo;

    /* PRACK header fields */
    public RAckHeader $rAck;
    public ScalarHeader $rSeq;

    /** @var string Message body */
    public string $body;

    /** @var array<string,Header> Additional/extension headers */
    public array $extraHeaders = [];

    /**
     * Parses a raw SIP message into a Request or Response
     *
     * @param string $text Raw SIP message
     * @param bool $ignoreBody Whether or not to ignore the Message body
     * @throws InvalidBodyLengthException
     * @throws InvalidCSeqValueException
     * @throws InvalidHeaderLineException
     * @throws InvalidHeaderSectionException
     * @return Message
     */
    public static function parse(string $text, bool $ignoreBody = false): Message
    {
        $text = ltrim($text, "\r\n");
        $lines = explode("\r\n", $text);

        $msg = (substr($text, 0, 7) === self::SIP_VERSION)
            ? new Response($lines[0])
            : new Request($lines[0]);

        $count = count($lines);
        $headers = [];

        for ($i = 1; $i < $count; $i++) {
            if (!isset($lines[$i][0])) {
                $boundary = $i;
                break;
            } else if (($lines[$i][0] === ' ') || ($lines[$i][0] === "\t")) {
                if (!isset($hvalue)) {
                    throw new InvalidHeaderLineException('Malformed Header-Line: ' . $lines[$i], Response::BAD_REQUEST);
                }

                $hvalue .= $lines[$i];
            } else {
                if (isset($hname, $hvalue) && strlen($hvalue)) {
                    $headers[$hname][] = $hvalue;
                }

                $delimPos = strpos($lines[$i], ':');

                /* Use of falsey is intentional, neither 0 nor false are not satisfactory here */
                if (!$delimPos) {
                    throw new InvalidHeaderLineException('Malformed Header-Line: ' . $lines[$i], Response::BAD_REQUEST);
                }

                $hname = strtolower(trim(substr($lines[$i], 0, $delimPos)));

                if (isset(self::COMPACT_HEADERS[$hname])) {
                    $hname = self::COMPACT_HEADERS[$hname];
                }

                if (!isset($headers[$hname])) {
                    $headers[$hname] = [];
                }

                $hvalue = substr($lines[$i], $delimPos + 1);
            }
        }

        if (isset($hname, $hvalue) && strlen($hvalue)) {
            $headers[$hname][] = $hvalue;
        }

        if (!isset($boundary)) {
            throw new InvalidHeaderSectionException('Malformed Message, missing CRLF separator after header section', Response::BAD_REQUEST);
        }

        foreach ($headers as $hname => $hbody) {
            try {
                switch ($hname) {
                    /* https://tools.ietf.org/html/rfc3261#section-20.42 */
                    case 'via':
                        $msg->via = ViaHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.20 */
                    case 'from':
                        $msg->from = FromHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.39 */
                    case 'to':
                        $msg->to = NameAddrHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.10 */
                    case 'contact':
                        $msg->contact = ContactHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.8 */
                    case 'call-id':
                        $msg->callId = CallIdHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.16 */
                    case 'cseq':
                        $msg->cSeq = CSeqHeader::parse($hbody);

                        if (isset($msg->method) && ($msg->method !== $msg->cSeq->method)) {
                            throw new InvalidCSeqValueException('Mismatched request method in CSeq header', Response::BAD_REQUEST);
                        }

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.22 */
                    case 'max-forwards':
                        $msg->maxForwards = MaxForwardsHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.14 */
                    case 'content-length':
                        $msg->contentLength = ScalarHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.19 */
                    case 'expires':
                        $msg->expires = ScalarHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.23 */
                    case 'min-expires':
                        $msg->minExpires = ScalarHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.33 */
                    case 'retry-after':
                        $msg->retryAfter = ScalarHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.38 */
                    case 'timestamp':
                        $msg->timestamp = ScalarHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.15 */
                    case 'content-type':
                        $msg->contentType = SingleValueWithParamsHeader::parse($hbody);

                        continue 2;

                    /* https://datatracker.ietf.org/doc/html/rfc6665#section-8.2.1 */
                    case 'event':
                        $msg->event = SingleValueWithParamsHeader::parse($hbody);

                        continue 2;

                    /* https://datatracker.ietf.org/doc/html/rfc6665#section-8.2.3 */
                    case 'subscription-state':
                        $msg->subscriptionState = SingleValueWithParamsHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.2 */
                    case 'accept-encoding':
                        $msg->acceptEncoding = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.5 */
                    case 'allow':
                        $msg->allow = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3265#section-7.2.2 */
                    case 'allow-events':
                        $msg->allowEvents = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.12 */
                    case 'content-encoding':
                        $msg->contentEncoding = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.21 */
                    case 'in-reply-to':
                        $msg->inReplyTo = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.32 */
                    case 'require':
                        $msg->require = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.37 */
                    case 'supported':
                        $msg->supported = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.40 */
                    case 'unsupported':
                        $msg->unsupported = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-8.1.1.9 */
                    case 'proxy-require':
                        $msg->proxyRequire = MultiValueHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.1 */
                    case 'accept':
                        $msg->accept = MultiValueWithParamsHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.3 */
                    case 'accept-language':
                        $msg->acceptLanguage = MultiValueWithParamsHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.9 */
                    case 'call-info':
                        $msg->callInfo = MultiValueWithParamsHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.13 */
                    case 'content-language':
                        $msg->contentLanguage = MultiValueWithParamsHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.31 */
                    case 'reply-to':
                        $msg->replyTo = NameAddrHeader::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.4 */
                    case 'alert-info':
                        $msg->alertInfo = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.6 */
                    case 'authentication-info':
                        $msg->authenticationInfo = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.7 */
                    case 'authorization':
                        /** @var AuthorizationHeader $aux */
                        $aux = AuthorizationHeader::parse($hbody);
                        $msg->authorization = $aux;

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.17 */
                    case 'date':
                        $msg->date = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.18 */
                    case 'error-info':
                        $msg->errorInfo = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.27 */
                    case 'proxy-authenticate':
                        /** @var AuthenticateHeader $aux */
                        $aux = AuthenticateHeader::parse($hbody);
                        $msg->proxyAuthenticate = $aux;

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.28 */
                    case 'proxy-authorization':
                        /** @var AuthorizationHeader $aux */
                        $aux = AuthorizationHeader::parse($hbody);
                        $msg->proxyAuthorization = $aux;

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.30 */
                    case 'record-route':
                        $msg->recordRoute = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.24 */
                    case 'mime-version':
                        $msg->mimeVersion = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.25 */
                    case 'organization':
                        $msg->organization = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.26 */
                    case 'priority':
                        $msg->priority = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.35 */
                    case 'route':
                        $msg->route = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.36 */
                    case 'subject':
                        $msg->subject = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.41 */
                    case 'user-agent':
                        $msg->userAgent = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.43 */
                    case 'warning':
                        $msg->warning = Header::parse($hbody);

                        continue 2;

                    /* https://tools.ietf.org/html/rfc3261#section-20.44 */
                    case 'www-authenticate':
                        /** @var AuthenticateHeader $aux */
                        $aux = AuthenticateHeader::parse($hbody);
                        $msg->wwwAuthenticate = $aux;

                        continue 2;

                    /* https://datatracker.ietf.org/doc/html/rfc3515#section-2.1 */
                    case 'refer-to':
                        $msg->referTo = Header::parse($hbody);

                        continue 2;

                    /* https://datatracker.ietf.org/doc/html/rfc3262#section-7.2 */
                    case 'rack':
                        $msg->rAck = RAckHeader::parse($hbody);

                        continue 2;

                    /* https://datatracker.ietf.org/doc/html/rfc3262#section-7.1 */
                    case 'rseq':
                        $msg->rSeq = ScalarHeader::parse($hbody);

                        continue 2;

                    default:
                        $msg->extraHeaders[$hname] = Header::parse($hbody);

                        continue 2;
                }
            } catch (SIPException $e) {
                $e->setStub($msg);

                throw $e;
            }
        }

        if ($ignoreBody) {
            return $msg;
        }

        $msg->body = implode("\r\n", array_slice($lines, $boundary + 1));
        $bodyLength = strlen($msg->body);

        if (isset($msg->contentLength)) {
            if ($bodyLength < $msg->contentLength->value) {
                $e = new InvalidBodyLengthException(
                    'Malformed message, content-length mismatch expected = ' .
                    $msg->contentLength->value .
                    ', actual = ' .
                    $bodyLength,
                    Response::BAD_REQUEST
                );
                $e->setStub($msg);

                throw $e;
            } else if ($bodyLength > $msg->contentLength->value) {
                /* Discard spurious noise per https://tools.ietf.org/html/rfc3261#section-18.3 */
                $msg->body = substr($msg->body, 0, $msg->contentLength->value);
            }
        }

        return $msg;
    }

    /**
     * SIP Header Renderer
     *
     * @param bool $compact Whether to output compact headers or not
     * @return string SIP headers as text
     */
    public function renderHeaders(bool $compact): string
    {
        $ret = '';

        if (isset($this->via)) {
            $ret .= $this->via->render($compact ? 'v' : 'Via');
        }

        if (isset($this->from)) {
            $ret .= $this->from->render($compact ? 'f' : 'From');
        }

        if (isset($this->to)) {
            $ret .= $this->to->render($compact ? 't' : 'To');
        }

        if (isset($this->contact)) {
            $ret .= $this->contact->render($compact ? 'm' : 'Contact');
        }

        if (isset($this->callId)) {
            $ret .= $this->callId->render($compact ? 'i' : 'Call-ID');
        }

        if (isset($this->cSeq)) {
            $ret .= $this->cSeq->render('CSeq');
        }

        if (isset($this->maxForwards)) {
            $ret .= $this->maxForwards->render('Max-Forwards');
        }

        if (isset($this->contentLength)) {
            $ret .= $this->contentLength->render($compact ? 'l' : 'Content-Length');
        }

        if (isset($this->expires)) {
            $ret .= $this->expires->render('Expires');
        }

        if (isset($this->minExpires)) {
            $ret .= $this->minExpires->render('Min-Expires');
        }

        if (isset($this->retryAfter)) {
            $ret .= $this->retryAfter->render('Retry-After');
        }

        if (isset($this->timestamp)) {
            $ret .= $this->timestamp->render('Timestamp');
        }

        if (isset($this->contentType)) {
            $ret .= $this->contentType->render($compact ? 'c' : 'Content-Type');
        }

        if (isset($this->event)) {
            $ret .= $this->event->render($compact ? 'o' : 'Event');
        }

        if (isset($this->subscriptionState)) {
            $ret .= $this->subscriptionState->render('Subscription-State');
        }

        if (isset($this->acceptEncoding)) {
            $ret .= $this->acceptEncoding->render('Accept-Encoding');
        }

        if (isset($this->allow)) {
            $ret .= $this->allow->render('Allow');
        }

        if (isset($this->allowEvents)) {
            $ret .= $this->allowEvents->render($compact ? 'u' : 'Allow-Events');
        }

        if (isset($this->contentEncoding)) {
            $ret .= $this->contentEncoding->render($compact ? 'e' : 'Content-Encoding');
        }

        if (isset($this->inReplyTo)) {
            $ret .= $this->inReplyTo->render('In-Reply-To');
        }

        if (isset($this->require)) {
            $ret .= $this->require->render('Require');
        }

        if (isset($this->supported)) {
            $ret .= $this->supported->render($compact ? 'k' : 'Supported');
        }

        if (isset($this->unsupported)) {
            $ret .= $this->unsupported->render('Unsupported');
        }

        if (isset($this->proxyRequire)) {
            $ret .= $this->proxyRequire->render('Proxy-Require');
        }

        if (isset($this->accept)) {
            $ret .= $this->accept->render('Accept');
        }

        if (isset($this->acceptLanguage)) {
            $ret .= $this->acceptLanguage->render('Accept-Language');
        }

        if (isset($this->callInfo)) {
            $ret .= $this->callInfo->render('Call-Info');
        }

        if (isset($this->contentLanguage)) {
            $ret .= $this->contentLanguage->render('Content-Language');
        }

        if (isset($this->replyTo)) {
            $ret .= $this->replyTo->render('Reply-To');
        }

        if (isset($this->alertInfo)) {
            $ret .= $this->alertInfo->render('Alert-Info');
        }

        if (isset($this->authenticationInfo)) {
            $ret .= $this->authenticationInfo->render('Authentication-Info');
        }

        if (isset($this->authorization)) {
            $ret .= $this->authorization->render('Authorization');
        }

        if (isset($this->date)) {
            $ret .= $this->date->render('Date');
        }

        if (isset($this->errorInfo)) {
            $ret .= $this->errorInfo->render('Error-Info');
        }

        if (isset($this->proxyAuthenticate)) {
            $ret .= $this->proxyAuthenticate->render('Proxy-Authenticate');
        }

        if (isset($this->proxyAuthorization)) {
            $ret .= $this->proxyAuthorization->render('Proxy-Authorization');
        }

        if (isset($this->recordRoute)) {
            $ret .= $this->recordRoute->render('Record-Route');
        }

        if (isset($this->mimeVersion)) {
            $ret .= $this->mimeVersion->render('MIME-Version');
        }

        if (isset($this->organization)) {
            $ret .= $this->organization->render('Organization');
        }

        if (isset($this->priority)) {
            $ret .= $this->priority->render('Priority');
        }

        if (isset($this->route)) {
            $ret .= $this->route->render('Route');
        }

        if (isset($this->subject)) {
            $ret .= $this->subject->render($compact ? 's' : 'Subject');
        }

        if (isset($this->userAgent)) {
            $ret .= $this->userAgent->render('User-Agent');
        }

        if (isset($this->warning)) {
            $ret .= $this->warning->render('Warning');
        }

        if (isset($this->wwwAuthenticate)) {
            $ret .= $this->wwwAuthenticate->render('WWW-Authenticate');
        }

        if (isset($this->referTo)) {
            $ret .= $this->referTo->render('Refer-To');
        }

        if (isset($this->rAck)) {
            $ret .= $this->rAck->render('RAck');
        }

        if (isset($this->rSeq)) {
            $ret .= $this->rSeq->render('RSeq');
        }

        foreach ($this->extraHeaders as $name => $header) {
            $ret .= $header->render($name);
        }

        return $ret;
    }
}
