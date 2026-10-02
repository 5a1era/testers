<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Test;

use Elros\Testing\Test\TestConfiguration;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TestConfigurationTest extends TestCase
{
    public function testConfigurationCanBeCreated(): void
    {
        $configuration = new TestConfiguration(
            timeLimit: 1800,
            attemptsLimit: 2,
            passingScore: 70,
            questionsCount: 10,
        );

        self::assertSame(1800, $configuration->getTimeLimit());
        self::assertSame(2, $configuration->getAttemptsLimit());
        self::assertSame(70, $configuration->getPassingScore());
        self::assertSame(10, $configuration->getQuestionsCount());
    }

    public function testTimeLimitMustBeGreaterThanZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TestConfiguration(
            timeLimit: 0,
            attemptsLimit: 2,
            passingScore: 70,
            questionsCount: 10,
        );
    }

    public function testAttemptsLimitMustBeGreaterThanZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TestConfiguration(
            timeLimit: 1800,
            attemptsLimit: 0,
            passingScore: 70,
            questionsCount: 10,
        );
    }

    public function testPassingScoreCannotBeLessThanZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TestConfiguration(
            timeLimit: 1800,
            attemptsLimit: 2,
            passingScore: -1,
            questionsCount: 10,
        );
    }

    public function testPassingScoreCannotBeGreaterThanOneHundred(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TestConfiguration(
            timeLimit: 1800,
            attemptsLimit: 2,
            passingScore: 101,
            questionsCount: 10,
        );
    }

    public function testQuestionsCountMustBeGreaterThanZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TestConfiguration(
            timeLimit: 1800,
            attemptsLimit: 2,
            passingScore: 70,
            questionsCount: 0,
        );
    }
}