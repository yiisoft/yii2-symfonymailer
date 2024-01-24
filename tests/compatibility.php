<?php

declare(strict_types=1);
/*
 * Ensures compatibility with PHPUnit < 6.x
 */

namespace PHPUnit\Framework\Constraint {
    if (!class_exists('PHPUnit\Framework\Constraint\Constraint') && class_exists('PHPUnit_Framework_Constraint')) {
        abstract class Constraint extends \PHPUnit\Framework\Constraint\Constraint {}
    }
}

namespace PHPUnit\Framework {
    if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {
        abstract class TestCase extends \PHPUnit\Framework\TestCase
        {
            /**
             * @param string $exception
             */
            public function expectException($exception)
            {
                $this->expectException($exception);
            }

            /**
             * @param string $message
             */
            public function expectExceptionMessage($message)
            {
                $this->expectException($this->getExpectedException());
                $this->expectExceptionMessage($message);
            }
        }
    }
}
