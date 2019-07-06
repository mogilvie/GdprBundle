<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 20/06/19
 * Time: 22:55
 */

namespace SpecShaper\GdprBundle\tests\Unit\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\Anonymise;

class AnonymiseTest extends \PHPUnit\Framework\TestCase
{
    public function testDispose()
    {

        $disposer = new Anonymise();

        $result = $disposer->dispose('DoctorWho');
        $this->assertTrue($result === "*********");

        $disposer = new Anonymise(['replaceWith' => null]);
        $result = $disposer->dispose('DoctorWho');

        $this->assertTrue($result === null);

        $disposer = new Anonymise(['replaceWith' => 'X']);
        $result = $disposer->dispose('DoctorWho');
        $this->assertTrue($result === "XXXXXXXXX");

        $disposer = new Anonymise(['replaceWith' => 'Anon']);
        $result = $disposer->dispose('DoctorWho');
        $this->assertTrue($result === "Anon");
    }
}
