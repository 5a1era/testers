<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Attempt;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Attempt\AttemptResultCalculator;
use Elros\Testing\Attempt\UserAnswer;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AttemptResultCalculatorTest extends TestCase
{
    public function testMultipleCorrectAnswersAreCalculatedCorrectly(): void
    {
        $answer1 = new AnswerOption(1, '2', true);
        $answer2 = new AnswerOption(2, '3', false);
        $answer3 = new AnswerOption(3, '4', true);

        $question = new Question(
            1,
            'Какие числа являются чётными?',
            [
                $answer1,
                $answer2,
                $answer3,
            ],
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(
                timeLimit: 30,
                attemptsLimit: 3,
                passingScore: 100,
            ),
        );

        $attempt = new Attempt(
            100,
            $test,
            500,
            new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question,
                [$answer1, $answer3],
            ),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(1, $result->getTotalQuestions());
        self::assertSame(1, $result->getAnsweredQuestions());
        self::assertSame(1, $result->getCorrectAnswers());
        self::assertSame(100, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testPartialMultipleAnswerIsIncorrect(): void
    {
        $answer1 = new AnswerOption(1, '2', true);
        $answer2 = new AnswerOption(2, '3', false);
        $answer3 = new AnswerOption(3, '4', true);

        $question = new Question(
            1,
            'Какие числа являются чётными?',
            [
                $answer1,
                $answer2,
                $answer3,
            ],
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(30, 3, 100),
        );

        $attempt = new Attempt(
            100,
            $test,
            500,
            new DateTimeImmutable(),
        );

        // Выбран только один из двух правильных вариантов.
        $attempt->answerQuestion(
            new UserAnswer(
                $question,
                [$answer1],
            ),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(0, $result->getCorrectAnswers());
        self::assertSame(0, $result->getScore());
        self::assertFalse($result->isPassed());
    }
    public function testSelectingIncorrectAnswerAlongWithCorrectAnswersIsIncorrect(): void
    {
        $answer1 = new AnswerOption(1, '2', true);
        $answer2 = new AnswerOption(2, '3', false);
        $answer3 = new AnswerOption(3, '4', true);

        $question = new Question(
            1,
            'Какие числа являются чётными?',
            [
                $answer1,
                $answer2,
                $answer3,
            ],
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(30, 3, 100),
        );

        $attempt = new Attempt(
            100,
            $test,
            500,
            new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question,
                [$answer1, $answer2, $answer3],
            ),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(0, $result->getCorrectAnswers());
        self::assertSame(0, $result->getScore());
        self::assertFalse($result->isPassed());
    }
}
