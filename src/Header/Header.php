<?php
/**
 * RTCKit\SIP\Header\Header Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
 * Generic Header class
 */
class Header
{
    /** @var list<string> Generic header field values */
    public array $values = [];

    final public function __construct() {}

    /**
     * Generic header value parser
     *
     * @param list<string> $hbody Header body
     * @return Header
     */
    public static function parse(array $hbody): Header
    {
        $ret = new static;

        foreach ($hbody as $hline) {
            $ret->values[] = trim($hline);
        }

        return $ret;
    }

    /**
     * Generic header value renderer
     *
     * @param string $hname Header field name
     * @return string
     */
    public function render(string $hname): string
    {
        $ret = '';

        foreach ($this->values as $value) {
            $ret .= "{$hname}: {$value}\r\n";
        }

        return $ret;
    }
}
