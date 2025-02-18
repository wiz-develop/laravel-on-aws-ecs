<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $date = new DateTimeImmutable();
        $this->assertTrue($date->format('Y') === (string)date('Y'));
    }
}
