<?php
/**
* RTCKit\SIP\ValueWithParams Class
*/
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
* Value with Parameters class
*/
class ValueWithParams
{
    /** @var string Header field value */
    public string $value;

    /** @var array<string, string> Parameters */
    public array $params = [];
}
