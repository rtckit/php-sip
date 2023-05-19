<a href="#php-sip-parsingrendering-library">
  <img loading="lazy" src="https://raw.github.com/rtckit/media/master/php-sip/readme-splash.png" alt="php-sip" class="width-full">
</a>

# PHP SIP Parsing/Rendering Library

[RFC 3261](https://tools.ietf.org/html/rfc3261) compliant SIP parsing and rendering library for PHP 7.4.

[![CI Status](https://github.com/rtckit/php-sip/workflows/CI/badge.svg)](https://github.com/rtckit/php-sip/actions/workflows/ci.yaml)
[![Psalm Type Coverage](https://shepherd.dev/github/rtckit/php-sip/coverage.svg)](https://shepherd.dev/github/rtckit/php-sip)
[![Latest Stable Version](https://poser.pugx.org/rtckit/sip/v/stable.png)](https://packagist.org/packages/rtckit/sip)
[![Installs on Packagist](https://img.shields.io/packagist/dt/rtckit/sip?color=blue&label=Installs%20on%20Packagist)](https://packagist.org/packages/rtckit/sip)
[![Test Coverage](https://api.codeclimate.com/v1/badges/aff5ee8e8ef3b51689c2/test_coverage)](https://codeclimate.com/github/rtckit/php-sip/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/aff5ee8e8ef3b51689c2/maintainability)](https://codeclimate.com/github/rtckit/php-sip/maintainability)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

## Quickstart

#### SIP Message Parsing

Once [installed](#installation), you can parse SIP messages right away as follows:

```php
/*
 * $text holds your SIP message as a string, for example
 * $text = 'REGISTER sip:192.168.0.1 SIP/2.0 /.../';
 */
$message = \RTCKit\SIP\Message::parse($text);

/* Outputs "RTCKit\SIP\Request" */
echo get_class($message) . PHP_EOL;

/* Outputs something similar to:
 * Protocol version:   SIP/2.0
 * Request method:     REGISTER
 * Request URI:        sip:192.168.0.1
 * Via:                192.168.0.2:5050
 * Via branch:         z9hG4bK.eAV4o0nXr
 * From scheme:        sip
 * From user:          buzz
 * From host:          192.168.0.1
 * From tag:           SFJbQ2oWh
 * To scheme:          sip
 * To user:            buzz
 * To host:            192.168.0.1
 * Sequence number:    20
 * Call ID:            ob0EYyuyC0
 */
printf("Protocol version:   %s" . PHP_EOL, $message->version);
printf("Request method:     %s" . PHP_EOL, $message->method);
printf("Request URI:        %s" . PHP_EOL, $message->uri);
printf("Via:                %s" . PHP_EOL, $message->via->values[0]->host);
printf("Via branch:         %s" . PHP_EOL, $message->via->values[0]->branch);
printf("From scheme:        %s" . PHP_EOL, $request->from->uri->scheme);
printf("From user:          %s" . PHP_EOL, $request->from->uri->user);
printf("From host:          %s" . PHP_EOL, $request->from->uri->host);
printf("From tag:           %s" . PHP_EOL, $request->from->tag);
printf("To scheme:          %s" . PHP_EOL, $request->to->uri->scheme);
printf("To user:            %s" . PHP_EOL, $request->to->uri->user);
printf("To host:            %s" . PHP_EOL, $request->to->uri->host);
printf("Sequence number:    %s" . PHP_EOL, $message->cSeq->sequence);
printf("Call ID:            %s" . PHP_EOL, $message->callId->value);
```

#### SIP Message Rendering

Rendering is the opposite action of parsing; for example, let's prepare a `200 OK` response for a `REGISTER` request:

```php
$response = new \RTCKit\SIP\Response;
$response->version = 'SIP/2.0';
$response->code = 200;

$response->via = new \RTCKit\SIP\Header\ViaHeader;
$response->via->values[0] = new \RTCKit\SIP\Header\ViaValue;
$response->via->values[0]->protocol = 'SIP';
$response->via->values[0]->version = '2.0';
$response->via->values[0]->transport = 'UDP';
$response->via->values[0]->host = '192.168.0.2:5050';
$response->via->values[0]->branch = 'z9hG4bK.eAV4o0nXr';

$response->from = new \RTCKit\SIP\Header\NameAddrHeader;
$response->from->uri = new \RTCKit\SIP\URI;
$response->from->uri->scheme = 'sip';
$response->from->uri->user = 'buzz';
$response->from->uri->host = '192.168.0.1';
$response->from->tag = 'SFJbQ2oWh';

$response->to = new \RTCKit\SIP\Header\NameAddrHeader;
$response->to->uri = new \RTCKit\SIP\URI;
$response->to->uri->scheme = 'sip';
$response->to->uri->user = 'buzz';
$response->to->uri->host = '192.168.0.1';
$response->to->tag = '8cQtUyH6N5N9K';

$response->cSeq = new \RTCKit\SIP\Header\CSeqHeader;
$response->cSeq->sequence = 20;
$response->cSeq->method = 'REGISTER';

$response->callId = new \RTCKit\SIP\Header\CallIdHeader;
$response->callId->value = 'ob0EYyuyC0';

$response->maxForwards = new \RTCKit\SIP\Header\ScalarHeader;
$response->maxForwards->value = 70;

$response->contact = new \RTCKit\SIP\Header\ContactHeader;
$response->contact->values[0] = new \RTCKit\SIP\Header\ContactValue;
$response->contact->values[0]->uri = new \RTCKit\SIP\URI;
$response->contact->values[0]->uri->scheme = 'sip';
$response->contact->values[0]->uri->user = 'buzz';
$response->contact->values[0]->uri->host = '192.168.0.2';
$response->contact->values[0]->uri->port = 5050;
$response->contact->values[0]->uri->transport = 'udp';
$response->contact->values[0]->expires = 3600;

$response->userAgent = new \RTCKit\SIP\Header\Header;
$response->userAgent->values[0] = 'MyDeskPhone/1.0.0';

/* Outputs:
 * SIP/2.0 200 OK
 * Via: SIP/2.0/UDP 192.168.0.2:5050;branch=z9hG4bK.eAV4o0nXr
 * From: <sip:buzz@192.168.0.1>;tag=SFJbQ2oWh
 * To: <sip:buzz@192.168.0.1>;tag=8cQtUyH6N5N9K
 * Contact: <sip:buzz@192.168.0.2:5050;transport=udp>;expires=3600
 * Call-ID: ob0EYyuyC0
 * CSeq: 20 REGISTER
 * Max-Forwards: 70
 * User-Agent: MyDeskPhone/1.0.0
 */
echo $response->render();
```

#### SIP Message Stream Parsing

If your use case involves a continuous data stream rather than individual messages, the `StreamParser` class can help; this is particularly useful for analyzing SIP trace files or packet captures, parsing SIP traffic over TCP etc.

```php
/* Instantiate the Stream Parser */
$parser = new \RTCKit\SIP\StreamParser;

$fp = fopen(/.../);

while (!feof($fp)) {
    $bytes = fread($fp, 256);

    /* The actual input string ($bytes) can be retrieved from any stream-like source */
    if ($parser->process($bytes, $messages) === \RTCKit\SIP\StreamParser::SUCCESS) {
        foreach ($messages as $message) {
            /*
             * $message is either a Request or a Response object, using
             * the same structure as messages returned by Message::parse()
             */
        }
    }
}
```

Lastly, the provided [examples](examples) are a good starting point.

## Requirements

**RTCKit\SIP** is compatible with PHP 7.4+ and has no external library and extension dependencies.

## Installation

You can add the library as project dependency using [Composer](https://getcomposer.org/):

```sh
composer require rtckit/sip
```

If you only need the library during development, for instance when used in your test suite, then you should add it as a development-only dependency:

```sh
composer require --dev rtckit/sip
```

## Tests

To run the test suite, clone this repository and then install dependencies via Composer:

```sh
composer install
```

Then, go to the project root and run:

```bash
php -d memory_limit=-1 ./vendor/bin/phpunit -c ./etc/phpunit.xml.dist
```

### Static Analysis

In order to ensure high code quality, **RTCKit\SIP** uses [PHPStan](https://github.com/phpstan/phpstan) and [Psalm](https://github.com/vimeo/psalm):

```sh
php -d memory_limit=-1 ./vendor/bin/phpstan analyse -c ./etc/phpstan.neon -n -vvv --ansi --level=max src
php -d memory_limit=-1 ./vendor/bin/psalm --config=./etc/psalm.xml
```

## License

MIT, see [LICENSE file](LICENSE).

### Acknowledgments

* [SIP Protocol Contributors/IETF Trust](https://www.ietf.org/standards/rfcs/)
* [PROTOS SIP Test Material](https://www.ee.oulu.fi/research/ouspg/PROTOS_Test-Suite_c07-sip) - Oulu University Secure Programming Group, Finland
* [lioneagle/sipparser Test Material](https://github.com/lioneagle/sipparser/blob/master/src/testdata/sip_msg.txt) (MIT license)

### Contributing

Bug reports (and small patches) can be submitted via the [issue tracker](https://github.com/rtckit/php-sip/issues). Forking the repository and submitting a Pull Request is preferred for substantial patches.
