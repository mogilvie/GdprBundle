<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 20/06/19
 * Time: 22:55
 */

namespace SpecShaper\GdprBundle\tests\Utils\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\RegexReplace;

class RegexReplaceTest extends \PHPUnit\Framework\TestCase
{
    public function testDispose()
    {

        $disposer = new RegexReplace(['pattern'=>"/tardis/"]);

        $result = $disposer->dispose('DoctorWho had a tardis, his tardis was blue.');
        $this->assertTrue($result === "DoctorWho had a ******, his ****** was blue.");

        $disposer = new RegexReplace(['pattern'=>"/tardis/", 'replaceWith' => "Redacted"]);
        $result = $disposer->dispose('DoctorWho had a tardis, his tardis was blue.');
        $this->assertTrue($result === "DoctorWho had a Redacted, his Redacted was blue.");

        $disposer = new RegexReplace(['pattern'=>"/[0-9]{2}/", 'replaceWith' =>  "X"]);
        $result = $disposer->dispose('DoctorWho had a 1 tardis, and 30 scarfs.');
        $this->assertTrue($result === "DoctorWho had a 1 tardis, and XX scarfs.");

    }
}
