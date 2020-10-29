<?php

declare(strict_types = 1);

namespace RTCKit\SIP\RFC4475;

use PHPUnit\Framework\TestCase;

class RFC4475Case extends TestCase
{
    public function loadFixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/../fixtures/rfc4475/' . $name . '.dat');
    }
}
