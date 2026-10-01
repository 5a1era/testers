<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Attempt;

use Elros\Testing\Attempt\AttemptResult;
use PHPUnit\Framework\TestCase;

final class AttemptResultTest extends TestCase
{
    public function testResultContainsCalculatedValues(): void
    {
        $result = new AttemptResult(
            totalQuestions: 10,
            answeredQuestions: 8,
            correctAnswers: 7,
            score: 70,
            passed: true,
        );

        self::assertSame(10, $result->getTotalQuestions());
        self::assertSame(8, $result->getAnsweredQuestions());
        self::assertSame(7, $result->getCorrectAnswers());
        self::assertSame(70, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testResultCanBeFailed(): void
    {
        $result = new AttemptResult(
            totalQuestions: 10,
            answeredQuestions: 10,
            correctAnswers: 5,
            score: 50,
            passed: false,
        );

        self::assertFalse($result->isPassed());
    }
}
