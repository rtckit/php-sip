<?php
/**
 * RTCKit\SIP\Header\MultiValueHeader Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Header;

/**
 * Multiple Value header class
 */
class MultiValueHeader
{
    /** @var list<string> Header field values */
    public array $values = [];

    final public function __construct() {}

    /**
     * Multpiple value header parser (no parameters)
     *
     * @param list<string> $hbody Header body
     * @return MultiValueHeader
     */
    public static function parse(array $hbody): MultiValueHeader
    {
        $ret = new static;

        foreach ($hbody as $hline) {
            $hvalues = explode(',', $hline);

            foreach ($hvalues as $hvalue) {
                $ret->values[] = trim($hvalue);
            }
        }

        return $ret;
    }

    /**
     * Multiple value header renderer
     *
     * @param string $hname Header field name
     * @return string
     */
    public function render(string $hname): string
    {
        return "{$hname}: " . implode(', ', $this->values) . "\r\n";
    }
}
