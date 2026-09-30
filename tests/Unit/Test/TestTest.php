<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Test;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use PHPUnit\Framework\TestCase;

final class TestTest extends TestCase
{
    public function testTestContainsQuestions(): void
    {
        $configuration = new TestConfiguration(30, 1, 80);

        $question1 = new Question(
            1,
            'Сколько будет 2 + 2?',
            [
                new AnswerOption(1, '3', false),
                new AnswerOption(2, '4', true),
                new AnswerOption(3, '5', false),
            ],
        );

        $question2 = new Question(
            2,
            'Сколько будет 3 + 3?',
            [
                new AnswerOption(4, '5', false),
                new AnswerOption(5, '6', true),
                new AnswerOption(6, '7', false),
            ],
        );

        $test = new Test(
            1,
            'Математика',
            'Базовый тест по математике',
            [
                $question1,
                $question2,
            ],
            $configuration
        );

        self::assertSame(1, $test->getId());
        self::assertSame('Математика', $test->getTitle());
        self::assertSame(
            'Базовый тест по математике',
            $test->getDescription()
        );

        self::assertCount(2, $test->getQuestions());
        self::assertSame($question1, $test->getQuestions()[0]);
        self::assertSame($question2, $test->getQuestions()[1]);
        self::assertSame($configuration, $test->getConfiguration());
    }
}
