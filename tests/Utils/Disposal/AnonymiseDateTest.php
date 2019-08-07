<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 20/06/19
 * Time: 22:55
 */

namespace SpecShaper\GdprBundle\tests\Utils\Disposal;

use SpecShaper\GdprBundle\Utils\Disposal\AnonymiseDate;

class AnonymiseDateTest extends \PHPUnit\Framework\TestCase
{
    public function testDispose()
    {

        $disposer = new AnonymiseDate(['type'=>AnonymiseDate::CLOSEST_DAY]);

        // Test by day round up.
        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-05-25 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-05-26 00:00:00');

        // Test by day round down.
        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-05-25 05:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-05-25 00:00:00');

        $disposer = new AnonymiseDate(['type'=>AnonymiseDate::CLOSEST_SUNDAY]);

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-06-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-06-16 00:00:00');

        // Test by closest sunday down.
        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-06-19 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-06-16 00:00:00');

        // Test by closest sunday up.
        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-06-20 05:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-06-23 00:00:00');

        $disposer = new AnonymiseDate(['type'=>AnonymiseDate::CURRENT_MONTH]);

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-06-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-06-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-01-29 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-01-01 00:00:00');

        $disposer = new AnonymiseDate(['type'=>AnonymiseDate::CURRENT_QUARTER]);

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-02-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-01-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-04-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-04-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-06-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-04-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-07-29 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-07-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-11-29 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-10-01 00:00:00');

        $disposer = new AnonymiseDate(['type'=>AnonymiseDate::CURRENT_YEAR]);

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-02-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2019-01-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2025-04-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2025-01-01 00:00:00');

        $disposer = new AnonymiseDate(['type'=>AnonymiseDate::CURRENT_DECADE]);

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-02-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2010-01-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2025-04-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2020-01-01 00:00:00');

        $disposer = new AnonymiseDate(['type'=>AnonymiseDate::CURRENT_CENTURY]);

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2019-02-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2000-01-01 00:00:00');

        $dateTime = date_create_from_format('Y-m-d H:i:s', '2125-04-16 18:45:45');
        $result = $disposer->dispose($dateTime);
        $this->assertTrue($result->format('Y-m-d H:i:s') === '2100-01-01 00:00:00');

    }
}
