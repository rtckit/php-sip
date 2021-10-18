<?php
/**
 * RTCKit\SIP\Header\ValueWithParams Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
 * Value with Params class
 */
class ValueWithParams
{
    /** @var string Header field value */
    public string $value;

    /** @var array<string,string> Parameters */
    public array $params = [];
}
