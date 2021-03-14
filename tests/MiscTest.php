<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses and reassembles a number of SIP transaction flows found in ./fixtures/misc
 */
class MiscTest extends TestCase
{
    public const CASE_DIR = __DIR__ . '/fixtures/misc';

    private array $flows = [];

    protected function setUp(): void
    {
        $dirs = scandir(self::CASE_DIR);

        foreach ($dirs as $dir) {
            $dir = self::CASE_DIR . '/' . $dir;

            if (!is_dir($dir) || ($dir[0] === '.')) {
                continue;
            }

            $files = scandir($dir);

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) != 'yaml') {
                    continue;
                }

                try {
                    $this->flows[$file] = Yaml::parse(file_get_contents($dir . '/' . $file));
                    $this->flows[$file] = preg_replace('~\R~u', "\r\n", $this->flows[$file]);
                } catch (\Throwable $t) {
                    echo $t->getMessage() . ' in flow ' . $file . "\n";

                    throw $t;
                }
            }
        }
    }

    public function testMisc()
    {
        foreach ($this->flows as $name => $flow) {
            foreach ($flow as $i => $text) {
                try {
                    $message = Message::parse($text);
                } catch (\Throwable $t) {
                    echo $t->getMessage() . ' in flow ' . $name . "\n";

                    throw $t;
                }

                /* Message could be parsed, no exceptions to be thrown */
                $this->assertInstanceOf(Message::class, $message);

                try {
                    $reassembled = $message->render();
                } catch (\Throwable $t) {
                    echo $t->getMessage() . ' in flow ' . $name . "\n";

                    throw $t;
                }

                /* Resulting message can be rendered, no exceptions to be thrown */
                $this->assertIsString($reassembled);

                try {
                    $reparsed = Message::parse($reassembled);
                } catch (\Throwable $t) {
                    echo $t->getMessage() . ' in flow ' . $name . "\n";

                    throw $t;
                }

                /* Reassembled message's message to be identical to the original message */
                $this->assertEquals($message, $reparsed, "In flow {$name}[{$i}]");
            }
        }
    }
}
