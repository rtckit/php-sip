<?php

declare(strict_types = 1);

namespace RTCKit\SIP;

use PHPUnit\Framework\TestCase;

/**
 * Loops through all INVITE requests from the PROTOS SIP test suite:
 * https://www.ee.oulu.fi/research/ouspg/PROTOS_Test-Suite_c07-sip
 *
 * Since this is a parsing/rendering library, many specific cases are not
 * in scope, yet we still want to validate we are able to parse such
 * SIP messages.
 */
class ProtosTest extends TestCase
{
    public const CASE_PHAR_URL = 'https://raw.githubusercontent.com/rtckit/protos-sip-test-cases/main/protos.phar';
    public const CASE_DIR = __DIR__ . '/fixtures/protos';
    public const CASE_PHAR = self::CASE_DIR . '/protos.phar';

    /* Possible goals for test case group */
    public const IGNORE = 0;
    public const MUST_PASS = 1;
    public const MUST_FAIL = 2;

    /* PROTOS test groups */
    public const CASE_MAP = [
        'valid' => [
            'first' => 0,
            'cases' => 1,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-method' => [
            'first' => 1,
            'cases' => 193,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-request-uri' => [
            'first' => 194,
            'cases' => 61,
            'criteria' => self::MUST_PASS,
            'exceptions' => [
                /* Excessive `:` leading to improper URI host/port */
                218,
                219,
                220,
                221,
                222,
                223,
                224,
                225,
                226,
                227,
                228,
                230,
                /* Excessive `@` leading to improper URI host/port */
                237,
                238,
                239,
                240,
                /* Not a valid hostname as it exceeds 253 ASCII characters */
                243,
                244,
                245,
                246,
                247,
                /* Incorrect TLD, exceeds 63 characters */
                249,
                250,
                251,
                252,
                253,
                254,
            ],
        ],
        'sip-version' => [
            'first' => 255,
            'cases' => 75,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-via-host' => [
            'first' => 330,
            'cases' => 106,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-via-hostcolon' => [
            'first' => 436,
            'cases' => 16,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-via-hostport' => [
            'first' => 452,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-via-version' => [
            'first' => 498,
            'cases' => 75,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-via-tag' => [
            'first' => 573,
            'cases' => 57,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-from-displayname' => [
            'first' => 630,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-from-tag' => [
            'first' => 823,
            'cases' => 57,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [
                /* These were likely meant to fail due to excessive length,
                   but we don't want to enforce this */
                864,
                865,
                866,
                867,
                868,
                869,
                870,
                /* These were likely meant to fail due to non-alphanumeric characters,
                   but we don't want to enforce this */
                865,
                866,
                867,
                868,
                869,
                870,
                871,
                872,
                873,
            ],
        ],
        'sip-from-colon' => [
            'first' => 880,
            'cases' => 16,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [
                /* No overflow in the first example */
                880,
            ],
        ],
        'sip-from-uri' => [
            'first' => 896,
            'cases' => 61,
            'criteria' => self::MUST_PASS,
            'exceptions' => [
                /* `:` overflow */
                920,
                921,
                922,
                923,
                924,
                925,
                926,
                927,
                928,
                929,
                930,
                932,
                /* `@` overflow */
                939,
                940,
                941,
                942,
                /* Invalid host URI component */
                945,
                946,
                947,
                948,
                949,
                951,
                952,
                953,
                954,
                955,
                956,
            ],
        ],
        'sip-contact-displayname' => [
            'first' => 957,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [
                /* Unescaped/Unenclosed wildcard * in display name */
                1061,
                1062,
                1063,
                1064,
                1065,
                1066,
                1067,
                1068,
                1069,
            ],
        ],
        'sip-contact-uri' => [
            'first' => 1150,
            'cases' => 61,
            'criteria' => self::MUST_PASS,
            'exceptions' => [
                /* `:` overflow */
                1174,
                1175,
                1176,
                1177,
                1178,
                1179,
                1180,
                1181,
                1182,
                1183,
                1184,
                1186,
                /* `@` overflow */
                1193,
                1194,
                1195,
                1196,
                /* Invalid host URI component */
                1199,
                1200,
                1201,
                1202,
                1203,
                1205,
                1206,
                1207,
                1208,
                1209,
                1210,
            ],
        ],
        'sip-contact-left-paranthesis' => [
            'first' => 1211,
            'cases' => 16,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-contact-right-paranthesis' => [
            'first' => 1227,
            'cases' => 16,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-to' => [
            'first' => 1243,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [
                /* General parsing errors, due to malformed header lines */
                1243,
                1271,
                1287,
            ],
        ],
        'sip-to-left-paranthesis' => [
            'first' => 1436,
            'cases' => 16,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-to-right-paranthesis' => [
            'first' => 1452,
            'cases' => 16,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-call-id-value' => [
            'first' => 1468,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-call-id-at' => [
            'first' => 1661,
            'cases' => 16,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-call-id-ip' => [
            'first' => 1677,
            'cases' => 106,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sip-expires' => [
            'first' => 1783,
            'cases' => 46,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [
                /* These go beyond the numeric/bound validation currently employed;
                   the application implementing this library should however recognize these exceptions */
                1783,
                1784,
                1786,
                1787,
                1789,
                1790,
                1791,
                1792,
                1794,
                1795,
                1797,
                1798,
                1799,
                1800,
                1801,
                1802,
                1803,
                1804,
                1806,
                1807,
                1808,
                1809,
                1811,
                1812,
            ],
        ],
        'sip-max-forwards' => [
            'first' => 1829,
            'cases' => 46,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [
                /* These go beyond the numeric/bound validation currently employed;
                   the application implementing this library should however recognize these exceptions */
                1829,
                1830,
                1832,
                1833,
                1835,
                1836,
                1837,
                1838,
                1840,
            ],
        ],
        'sip-cseq-integer' => [
            'first' => 1875,
            'cases' => 46,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [
                /* These go beyond the numeric/bound validation currently employed;
                   the application implementing this library should however recognize these exceptions */
                1875,
                1876,
                1878,
                1879,
                1881,
                1882,
                1883,
                1884,
                1886,
                1887,
                1889,
                1890,
                1891,
                1892,
                1893,
                1894,
                1895,
                1896,
                1898,
                1899,
                1900,
                1901,
                1903,
                1904,
            ],
        ],
        'sip-cseq-string' => [
            'first' => 1921,
            'cases' => 193,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sip-content-type' => [
            'first' => 2114,
            'cases' => 247,
            'criteria' => self::MUST_PASS,
            'exceptions' => [
                /* Outright malformed header */
                2114,
                2142,
                2143,
                2144,
                2145,
                2146,
                2147,
                2148,
                2149,
                2150,
                2151,
                2152,
                2153,
                2154,
                2155,
                2156,
                2157,
                2158,
                2356,
            ],
        ],
        'sip-content-length' => [
            'first' => 2361,
            'cases' => 46,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [
                /* This implementation discards the excess body (considering it as spurious noise) */
                2361,
                2362,
                2364,
                2365,
                2367,
                2368,
                2369,
                2370,
            ],
        ],
        'sip-request-crlf' => [
            'first' => 2407,
            'cases' => 10,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'crlf-request' => [
            'first' => 2417,
            'cases' => 10,
            'criteria' => self::MUST_FAIL,
            'exceptions' => [],
        ],
        'sdp-attribute-crlf' => [
            'first' => 2427,
            'cases' => 10,
            'criteria' => self::MUST_PASS, /* SDP parsing is out of scope */
            'exceptions' => [],
        ],
        'sdp-proto-v-identifier' => [
            'first' => 2437,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-proto-v-equal' => [
            'first' => 2630,
            'cases' => 16,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-proto-v-integer' => [
            'first' => 2646,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-origin-username' => [
            'first' => 2692,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-origin-sessionid' => [
            'first' => 2885,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-origin-networktype' => [
            'first' => 2931,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-origin-ip' => [
            'first' => 3124,
            'cases' => 106,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-session' => [
            'first' => 3230,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-connection-networktype' => [
            'first' => 3423,
            'cases' => 188,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-connection-ip' => [
            'first' => 3611,
            'cases' => 106,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-time-start' => [
            'first' => 3717,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-time-stop' => [
            'first' => 3763,
            'cases' => 1,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-media-media' => [
            'first' => 3764,
            'cases' => 193,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-media-port' => [
            'first' => 3957,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-media-transport' => [
            'first' => 4003,
            'cases' => 118,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-media-type' => [
            'first' => 4121,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-attribute-rtpmap' => [
            'first' => 4167,
            'cases' => 118,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-attribute-colon' => [
            'first' => 4285,
            'cases' => 16,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-attribute-payloadtype' => [
            'first' => 4301,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-attribute-encodingname' => [
            'first' => 4347,
            'cases' => 118,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-attribute-slash' => [
            'first' => 4465,
            'cases' => 16,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
        'sdp-attribute-clockrate' => [
            'first' => 4481,
            'cases' => 46,
            'criteria' => self::MUST_PASS,
            'exceptions' => [],
        ],
    ];

    private array $flows = [];

    protected function setUp(): void
    {
        if (!is_dir(self::CASE_DIR)) {
            mkdir(self::CASE_DIR, 0777);
        }

        if (!is_file(self::CASE_PHAR)) {
            file_put_contents(self::CASE_PHAR, fopen(self::CASE_PHAR_URL, 'r'));
        }

        $cases = scandir('phar://' . self::CASE_PHAR);

        foreach ($cases as $caseNum) {
            if (is_dir($caseNum) || ($caseNum[0] === '.')) {
                continue;
            }

            $case = 'phar://' . self::CASE_PHAR . '/' . $caseNum;
            $raw = file_get_contents($case);

            $invitePduSize = (int) explode(' ', substr($raw, 0, 16))[0];
            $invitePduPreamble = (int) log10($invitePduSize) + 2;
            $rawInvitePDU = substr($raw, $invitePduPreamble, $invitePduSize);

            $teardownPduSize = (int) explode(' ', substr($raw, $invitePduPreamble + $invitePduSize, 16))[0];
            $teardownPduPreamble = (int) log10($teardownPduSize) + 2;
            $rawResponsePDU = substr($raw, $invitePduPreamble + $invitePduSize + $teardownPduPreamble, $teardownPduSize);

            $replacements = [
                '<To>' => $this->randomHex(12) . '@' . $this->randomHex(16) . '.' . $this->randomHex(3),
                '<From-Address>' => $this->randomHex(16) . '.' . $this->randomHex(3),
                '<Local-Port>' => (string) rand(5000, 6000),
                '<Branch-ID>' => $this->randomHex(16),
                '<From>' => $this->randomHex(12) . '@' . $this->randomHex(16) . '.' . $this->randomHex(3),
                '<Call-ID>' => $this->randomHex(32),
                '<CSeq>' => (string) rand(8000, 12000),
                '<From-IP>' => (string) rand(0, 255) . '.' . (string) rand(0, 255) . '.' . (string) rand(0, 255) . '.' . (string) rand(0, 255),
                '<Teardown-Method>' => 'CANCEL',
            ];

            $invitePDU = strtr($rawInvitePDU, $replacements);
            $teardownPDU = strtr($rawResponsePDU, $replacements);

            $bodyPos = strpos($invitePDU, "\r\n\r\n") + 4;
            $invitePDU = str_replace('<Content-Length>', (string) (strlen($invitePDU) - $bodyPos), $invitePDU);

            $this->flows[$caseNum] = [
                'request' => $invitePDU,
                'teardown' => $teardownPDU,
            ];
        }
    }

    public function testProtos()
    {
        $results = [];

        foreach ($this->flows as $caseNum => $body) {
            $num = (int) $caseNum;
            $pass = null;

            try {
                $msg = Message::parse($body['request']);
                $pass = true;
            } catch (\Throwable $t) {
                $pass = false;
            }

            foreach (self::CASE_MAP as $name => $params) {
                if (($num >= $params['first']) && ($num < $params['first'] + $params['cases'])) {
                    if (!isset($results[$name])) {
                        $results[$name] = [
                            'pass' => 0,
                            'fail' => 0,
                            'success' => true,
                        ];
                    }

                    break;
                }
            }

            $results[$name][$pass ? 'pass' : 'fail']++;

            if ($pass) {
                /* Message could be parsed, no exceptions to be thrown */
                $this->assertInstanceOf(Message::class, $msg);

                try {
                    $reassembled = $msg->render();
                } catch (\Throwable $t) {
                    echo $t->getMessage() . ' in case ' . $num . "\n";

                    throw $t;
                }

                /* Resulting message can be rendered, no exceptions to be thrown */
                $this->assertIsString($reassembled);

                try {
                    $reparsed = Message::parse($reassembled);
                } catch (\Throwable $t) {
                    echo $t->getMessage() . ' in flow ' . $num . "\n";

                    throw $t;
                }

                /* Reassembled message's message to be identical to the original message */
                $this->assertEquals($msg, $reparsed, "In case {$num}");

                /* Also, let's parse the teardown request too */
                $teardown = Message::parse($body['teardown']);

                $this->assertInstanceOf(Message::class, $msg);
                $this->assertEquals($teardown, Message::parse($teardown->render()));
            }

            if (!in_array($num, $params['exceptions'])) {
                /* This is solely for debugging purposes */
                if (($params['criteria'] === self::MUST_PASS) && !$pass) {
                    $results[$name]['success'] = false;

                    printf("Test %d should have passed: %s\n", $num, $t->getMessage());
                } else if (($params['criteria'] === self::MUST_FAIL) && $pass) {
                    $results[$name]['success'] = false;

                    printf("Test %d should have failed\n", $num);
                }

                if ($params['criteria'] === self::MUST_PASS) {
                    $this->assertTrue($pass, 'Test case ' . $num);
                } else if ($params['criteria'] === self::MUST_FAIL) {
                    $this->assertFalse($pass, 'Test case ' . $num);
                }
            }
        }

        if (in_array('--debug', $_SERVER['argv'], true)) {
            printf("=====[PROTOS SIP Test Suite]==========================================================\n");
            printf("Name                                     | First  | Cases  | Passes | Fails  | Success\n");
            printf("--------------------------------------------------------------------------------------\n");

            foreach ($results as $name => $result) {
                printf(
                    "%40s | %6d | %6d | %6d | %6d | %s\n",
                    $name,
                    self::CASE_MAP[$name]['first'],
                    self::CASE_MAP[$name]['cases'],
                    $result['pass'],
                    $result['fail'],
                    (self::CASE_MAP[$name]['criteria'] === self::IGNORE) ? '-' : ($result['success'] ? 'Yes' : 'No')
                );
            }

            printf("======================================================================================\n");
        }
    }

    private function randomHex(int $length): string
    {
        $ret = '';
        $rounds = ceil($length / 32);

        for ($i = 0; $i <= $rounds; $i++) {
            $ret .= md5(uniqid((string) rand(), true));
        }

        return substr($ret, 0, $length);
    }
}
