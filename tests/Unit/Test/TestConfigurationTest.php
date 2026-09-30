<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Test;

use Elros\Testing\Test\TestConfiguration;
use PHPUnit\Framework\TestCase;

final class TestConfigurationTest extends TestCase
{
    public function testConfigurationCanBeCreated(): void
    {
        $configuration = new TestConfiguration(30, 2, 70);

        self::assertSame(30, $configuration->getTimeLimit());
        self::assertSame(2, $configuration->getAttemptsLimit());
        self::assertSame(70, $configuration->getPassingScore());
    }

    public function testTimeLimitMustBePositive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TestConfiguration(0, 2, 70);
    }

    public function testAttemptsLimitMustBePositive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TestConfiguration(30, 0, 70);
    }

    public function testPassingScoreCannotBeLessThanZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TestConfiguration(30, 2, -1);
    }

    public function testPassingScoreCannotBeGreaterThanOneHundred(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TestConfiguration(30, 2, 101);
    }
}
