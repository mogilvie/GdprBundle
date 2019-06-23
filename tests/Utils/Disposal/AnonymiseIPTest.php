<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 20/06/19
 * Time: 22:55
 */

namespace SpecShaper\GdpprBundle\tests\Unit\Disposal;


use SpecShaper\GdprBundle\Utils\Disposal\AnonymiseIP;

class AnonymiseIPTest extends \PHPUnit\Framework\TestCase
{
    public function testDispose()
    {

        $disposer = new AnonymiseIP();

        // Test IPv4 address.
        $result = $disposer->dispose('195.25.44.6');
        $this->assertTrue($result === "255.255.255.0");

        $result = $disposer->dispose('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $this->assertTrue($result === "ffff:ffff:ffff::");

        $disposer = new AnonymiseIP(['anonIPv4' => '***.***.***.***']);

        // Test IPv4 address.
        $result = $disposer->dispose('195.25.44.6');
        $this->assertTrue($result === '***.***.***.***');

        $result = $disposer->dispose('FE80:0000:0000:0000:0202:B3FF:FE1E:8329');
        $this->assertTrue($result === "ffff:ffff:ffff::");
    }
}
