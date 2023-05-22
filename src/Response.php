<?php
/**
 * RTCKit\SIP\Response Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP;

use RTCKit\SIP\Exception\InvalidMessageStartLineException;
use RTCKit\SIP\Exception\InvalidProtocolVersionException;
use RTCKit\SIP\Exception\InvalidStatusCodeException;

/**
 * SIP Response class
 */
class Response extends Message
{
    /* Extended search being performed may take a significant time so a forking
    proxy must send a 100 Trying response. */
    public const TRYING = 100;

    /* Destination user agent received INVITE, and is alerting user of call. */
    public const RINGING = 180;

    /* Servers can optionally send this response to indicate a call is being forwarded. */
    public const CALL_IS_BEING_FORWARDED = 181;

    /* Indicates that the destination was temporarily unavailable, so the server
    has queued the call until the destination is available. A server may send multiple
    182 responses to update progress of the queue. */
    public const QUEUED = 182;

    /* This response may be used to send extra information for a call which is
    still being set up. */
    public const SESSION_IN_PROGRESS = 183;

    /* Can be used by User Agent Server to indicate to upstream SIP entities
    (including the User Agent Client (UAC)) that an early dialog has been terminated. */
    public const EARLY_DIALOG_TERMINATED = 199;

    /* Indicates the request was successful. */
    public const OK = 200;

    /* (Deprecated) Indicates that the request has been accepted for processing,
    but the processing has not been completed. */
    public const ACCEPTED = 202;

    /* HTTP 1.1. The server successfully processed the request but is returning
    information from another source. */
    public const NON_AUTHORITATIVE_INFORMATION = 203;

    /* Indicates the request was successful, but the corresponding response will
    not be received. */
    public const NO_NOTIFICATION = 204;

    /* The address resolved to one of several options for the user or client to choose
    between, which are listed in the message body or the message`s Contact fields. */
    public const MULTIPLE_CHOICES = 300;

    /* The original Request-URI is no longer valid, the new address is given in the
    Contact header field, and the client should update any records of the original
    Request-URI with the new value. */
    public const MOVED_PERMANENTLY = 301;

    /* The client should try at the address in the Contact field. If an Expires
    field is present, the client may cache the result for that period of time. */
    public const MOVED_TEMPORARILY = 302;

    /* The Contact field details a proxy that must be used to access the requested destination. */
    public const USE_PROXY = 305;

    /* The call failed, but alternatives are detailed in the message body. */
    public const ALTERNATIVE_SERVICE = 380;

    /* The request could not be understood due to malformed syntax. */
    public const BAD_REQUEST = 400;

    /* The request requires user authentication. This response is issued by UASs and registrars. */
    public const UNAUTHORIZED = 401;

    /* Reserved for future use. */
    public const PAYMENT_REQUIRED = 402;

    /* The server understood the request, but is refusing to fulfil it. */
    public const FORBIDDEN = 403;

    /* The server has definitive information that the user does not exist at the
    domain specified in the Request-URI. This status is also returned if the domain
    in the Request-URI does not match any of the domains handled by the recipient of
    the request. */
    public const NOT_FOUND = 404;

    /* The method specified in the Request-Line is understood, but not allowed for
    the address identified by the Request-URI. */
    public const METHOD_NOT_ALLOWED = 405;

    /* The resource identified by the request is only capable of generating response
    entities that have content characteristics but not acceptable according to the
    Accept header field sent in the request. */
    public const NOT_ACCEPTABLE = 406;

    /* The request requires user authentication. This response is issued by proxys. */
    public const PROXY_AUTHENTICATION_REQUIRED = 407;

    /* Couldnt find the user in time. The server could not produce a response within
    a suitable amount of time, for example, if it could not determine the location of
    the user in time. The client MAY repeat the request without modifications at any
    later time. */
    public const REQUEST_TIMEOUT = 408;

    /* (Deprecated) User already registered. Deprecated by omission from later RFCs
    and by non-registration with the IANA. */
    public const CONFLICT = 409;

    /* The user existed once, but is not available here any more. */
    public const GONE = 410;

    /* (Deprecated) The server will not accept the request without a valid
    Content-Length. Deprecated by omission from later RFCs and by non-registration
    with the IANA. */
    public const LENGTH_REQUIRED = 411;

    /* The given precondition has not been met. */
    public const CONDITIONAL_REQUEST_FAILED = 412;

    /* Request body too large. */
    public const REQUEST_ENTITY_TOO_LARGE = 413;

    /* The server is refusing to service the request because the Request-URI is
    longer than the server is willing to interpret. */
    public const REQUEST_URI_TOO_LONG = 414;

    /* Request body in a format not supported. */
    public const UNSUPPORTED_MEDIA_TYPE = 415;

    /* Request-URI is unknown to the server. */
    public const UNSUPPORTED_URI_SCHEME = 416;

    /* There was a resource-priority option tag, but no Resource-Priority header. */
    public const UNKNOWN_RESOURCE_PRIORITY = 417;

    /* Bad SIP_Protocol Extension used, not understood by the server. */
    public const BAD_EXTENSION = 420;

    /* The server needs a specific extension not listed in the Supported header. */
    public const EXTENSION_REQUIRED = 421;

    /* The received request contains a Session-Expires header field with a duration
    below the minimum timer. */
    public const SESSION_INTERVAL_TOO_SMALL = 422;

    /* Expiration time of the resource is too short. */
    public const INTERVAL_TOO_BRIEF = 423;

    /* The request's location content was malformed or otherwise unsatisfactory. */
    public const BAD_LOCATION_INFORMATION = 424;

    /* The server policy requires an Identity header, and one has not been provided. */
    public const USE_IDENTITY_HEADER = 428;

    /* The server did not receive a valid Referred-By token on the request.. */
    public const PROVIDE_REFERRER_IDENTITY = 429;

    /* A specific flow to a user agent has failed, although other flows may succeed.
    This response is intended for use between proxy devices, and should not be seen
    by an endpoint (and if it is seen by one, should be treated as a 400 Bad Request
    response) */
    public const FLOW_FAILED = 430;

    /* The request has been rejected because it was anonymous. */
    public const ANONYMITY_DISALLOWED = 430;

    /* The request has an Identity-Info header, and the URI scheme in that header
    cannot be dereferenced. */
    public const BAD_IDENTITY_INFO = 436;

    /* The server was unable to validate a certificate for the domain that signed the request. */
    public const UNSUPPORTED_CERTIFICATE = 437;

    /* The server obtained a valid certificate that the request claimed was used to
    sign the request, but was unable to verify that signature. */
    public const INVALID_IDENTITY_HEADER = 438;

    /* The first outbound proxy the user is attempting to register through does not
    support the "outbound" feature of RFC 5626, although the registrar does. */
    public const FIRST_HOP_LACKS_OUTBOUND_SUPPORT = 439;

    /* The source of the request did not have the permission of the recipient to make
    such a request. */
    public const CONSENT_NEEDED = 470;

    /* Callee currently unavailable. */
    public const TEMPORARILY_UNAVAILABLE = 480;

    /* Server received a request that does not match any dialog or transaction. */
    public const CALL_TRANSACTION_DOES_NOT_EXIST = 481;

    /* Server has detected a loop. */
    public const LOOP_DETECTED = 482;

    /* Max-Forwards header has reached the value '0'. */
    public const TOO_MANY_HOPS = 483;

    /* Request-URI incomplete. */
    public const ADDRESS_INCOMPLETE = 484;

    /* Request-URI is ambiguous. */
    public const AMBIGUOUS = 485;

    /* Callee is busy. */
    public const BUSY_HERE = 486;

    /* Request has terminated by bye or cancel. */
    public const REQUEST_TERMINATED = 487;

    /* Some aspect of the session description or the Request-URI is not acceptable. */
    public const NOT_ACCEPTABLE_HERE = 488;

    /* The server did not understand an event package specified in an Event header field. */
    public const BAD_EVENT = 489;

    /* Server has some pending request from the same dialog. */
    public const REQUEST_PENDING = 491;

    /* Request contains an encrypted MIME body, which recipient can not decrypt. */
    public const UNDECIPHERABLE = 493;

    /* The server has received a request that requires a negotiated security mechanism,
    and the response contains a list of suitable security mechanisms for the requester
    to choose between, or a digest authentication challenge. */
    public const SECURITY_AGREEMENT_REQUIRED = 494;

    /* The server could not fulfill the request due to some unexpected condition. */
    public const INTERNAL_SERVER_ERROR = 500;

    /* The server does not have the ability to fulfill the request, such as because it
    does not recognize the request method. (Compare with 405 Method Not Allowed, where
    the server recognizes the method but does not allow or support it.) */
    public const NOT_IMPLEMENTED = 501;

    /* The server is acting as a gateway or proxy, and received an invalid response
    from a downstream server while attempting to fulfill the request. */
    public const BAD_GATEWAY = 502;

    /* The server is undergoing maintenance or is temporarily overloaded and so cannot
    process the request. A "Retry-After" header field may specify when the client may
    reattempt its request. */
    public const SERVICE_UNAVAILABLE = 503;

    /* The server attempted to access another server in attempting to process the
    request, and did not receive a prompt response. */
    public const SERVER_TIME_OUT = 504;

    /* The SIP protocol version in the request is not supported by the server. */
    public const VERSION_NOT_SUPPORTED = 505;

    /* The request message length is longer than the server can process. */
    public const MESSAGE_TOO_LARGE = 513;

    /* The server is unable or unwilling to meet some constraints specified in the offer. */
    public const PRECONDITION_FAILURE = 580;

    /* All possible destinations are busy. Unlike the 486 response, this response
    indicates the destination knows there are no alternative destinations (such as
    a voicemail server) able to accept the call. */
    public const BUSY_EVERYWHERE = 600;

    /* The destination does not wish to participate in the call, or cannot do so,
    and additionally the destination knows there are no alternative destinations
    (such as a voicemail server) willing to accept the call. */
    public const DECLINE = 603;

    /* The server has authoritative information that the requested user does not exist anywhere. */
    public const DOES_NOT_EXIST_ANYWHERE = 604;

    /* The user's agent was contacted successfully but some aspects of the session
    description such as the requested media, bandwidth, or addressing style were not acceptable. */
    public const GLOBAL_NOT_ACCEPTABLE = 606;

    public const REASONS = [
        100 => 'Trying',
        180 => 'Ringing',
        181 => 'Call is Being Forwarded',
        182 => 'Queued',
        183 => 'Session in Progress',
        199 => 'Early Dialog Terminated',
        200 => 'OK',
        202 => 'Accepted',
        203 => 'Non-authoritative information',
        204 => 'No Notification',
        300 => 'Multiple choices',
        301 => 'Moved permanently',
        302 => 'Moved Temporarily',
        305 => 'Use proxy',
        380 => 'Alternative Service',
        400 => 'Bad request',
        401 => 'Unauthorized',
        402 => 'Payment required',
        403 => 'Forbidden',
        404 => 'Not found',
        405 => 'Method not allowed',
        406 => 'Not acceptable',
        407 => 'Proxy authentication required',
        408 => 'Request timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length required',
        412 => 'Conditional Request failed',
        413 => 'Request entity too large',
        414 => 'Request-URI too long',
        415 => 'Unsupported media type',
        416 => 'Unsupported URI Scheme',
        417 => 'Unknown Resource-Priority',
        420 => 'Bad Extension',
        421 => 'Extension Required',
        422 => 'Session Interval Too Small',
        423 => 'Interval Too Brief',
        424 => 'Bad Location Information',
        428 => 'Use Identity Header',
        429 => 'Provide Referrer Identity',
        430 => 'Anonymity Disallowed',
        436 => 'Bad Identity-Info',
        437 => 'Unsupported Certificate',
        438 => 'Invalid Identity Header',
        439 => 'First Hop Lacks Outbound Support',
        470 => 'Consent Needed',
        480 => 'Temporarily Unavailable',
        481 => 'Call/Transaction Does Not Exist',
        482 => 'Loop Detected',
        483 => 'Too Many Hops',
        484 => 'Address Incomplete',
        485 => 'Ambiguous',
        486 => 'Busy Here',
        487 => 'Request Terminated',
        488 => 'Not Acceptable Here',
        489 => 'Bad Event',
        491 => 'Request Pending',
        493 => 'Undecipherable',
        494 => 'Security Agreement Required',
        500 => 'Internal server error',
        501 => 'Not implemented',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
        504 => 'Server Time-out',
        505 => 'SIP version not supported',
        513 => 'Message Too Large',
        580 => 'Precondition Failure',
        600 => 'Busy Everywhere',
        603 => 'Decline',
        604 => 'Does Not Exist Anywhere',
        606 => 'Not Acceptable',
    ];

    /** @var int Response code */
    public int $code;

    /** @var string Response reason */
    public string $reason;

    /**
     * SIP Response constructor
     *
     * @param ?string $startLine Raw message start line
     * @throws InvalidMessageStartLineException
     * @throws InvalidProtocolVersionException
     * @throws InvalidStatusCodeException
     */
    public function __construct(?string $startLine = null)
    {
        if (is_null($startLine)) {
            return;
        }

        $sttsLine = explode(' ', $startLine, 3);

        if (count($sttsLine) !== 3) {
            throw new InvalidMessageStartLineException('Malformed Status-Line: ' . $startLine, self::BAD_REQUEST);
        }

        if ($sttsLine[0] !== Message::SIP_VERSION) {
            throw new InvalidProtocolVersionException('Unsupported SIP version: ' . $sttsLine[0], self::VERSION_NOT_SUPPORTED);
        }

        $this->code = (int) $sttsLine[1];

        if (($this->code < 100) || ($this->code > 699)) {
            throw new InvalidStatusCodeException('Unknown SIP/2.0 response response code: ' . $this->code, self::BAD_REQUEST);
        }

        $this->version = $sttsLine[0];
        $this->reason = $sttsLine[2];
    }

    /**
     * SIP Response Renderer
     *
     * @param bool $compact Whether to output compact headers or not
     * @throws InvalidStatusCodeException
     * @return string SIP response as text
     */
    public function render(bool $compact = false): string
    {
        if (!isset($this->code)) {
            throw new InvalidStatusCodeException('Missing response code');
        }

        if (($this->code < 100) || ($this->code > 699)) {
            throw new InvalidStatusCodeException('Unknown SIP/2.0 response response code: ' . $this->code);
        }

        if (!isset($this->reason[0])) {
            if (isset(self::REASONS[$this->code])) {
                $this->reason = self::REASONS[$this->code];
            } else {
                throw new InvalidStatusCodeException('Unknown SIP/2.0 response reason for code: ' . $this->code);
            }
        }

        $this->version ??= Message::SIP_VERSION;
        $this->body ??= '';
        $headers = $this->renderHeaders($compact);

        return "{$this->version} {$this->code} {$this->reason}\r\n{$headers}\r\n{$this->body}";
    }
}
