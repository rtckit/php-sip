<?php
/**
 * RTCKit\SIP\Auth\ParamsInterface Interface
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Auth;

/**
 * Authentication scheme params interface
 */
interface ParamsInterface
{
    /**
     * Parses authentication scheme parameters out of a string input
     *
     * @param string $input Unparsed authentication parameters
     * @return ParamsInterface Parsed parameters
     */
    public static function parse(string $input): ParamsInterface;

    /**
     * Renders authentication scheme parameters as string
     *
     * @return string Authentication parameters
     */
    public function render(): string;
}
