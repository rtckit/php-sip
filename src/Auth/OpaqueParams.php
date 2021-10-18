<?php
/**
 * RTCKit\SIP\Auth\OpaqueParams Class
 */
declare(strict_types = 1);

namespace RTCKit\SIP\Auth;

/**
 * Opaque authentication scheme parameters class
 */
final class OpaqueParams implements ParamsInterface
{
    /** @var string Opaque authentication scheme parameters in original form */
    public string $verbatim;

    /**
     * Captures opaque authentication scheme parameters out of a string input
     *
     * @param string $input Unparsed authentication parameters
     * @return ParamsInterface Wrapped parameters
     */
    public static function parse(string $input): ParamsInterface
    {
        $params = new static;
        $params->verbatim = $input;

        return $params;
    }

    /**
     * Renders opaque authentication scheme parameters as string
     *
     * @return string Opaque authentication parameters
     */
    public function render(): string
    {
        return $this->verbatim;
    }
}
