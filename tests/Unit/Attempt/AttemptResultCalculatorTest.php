<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Attempt;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Attempt\AttemptResultCalculator;
use Elros\Testing\Attempt\UserAnswer;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Question\QuestionType;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AttemptResultCalculatorTest extends TestCase
{
    public function testSingleQuestionWithCorrectAnswerGetsFullScore(): void
    {
        $correctAnswer = new AnswerOption(1, '4', true);
        $wrongAnswer = new AnswerOption(2, '5', false);

        $question = new Question(
            1,
            '2 + 2 = ?',
            [$correctAnswer, $wrongAnswer],
            QuestionType::SINGLE,
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(
                timeLimit: 60,
                attemptsLimit: 1,
                passingScore: 70,
                questionsCount: 10,
            ),
        );

        $attempt = new Attempt(
            1,
            $test,
            100,
            new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer($question, [$correctAnswer]),
            new DateTimeImmutable('2026-10-01 10:31:00'),
        );

        $result = (new AttemptResultCalculator())->calculate($attempt);

        self::assertSame(1, $result->getTotalQuestions());
        self::assertSame(1, $result->getAnsweredQuestions());
        self::assertSame(1, $result->getCorrectAnswers());
        self::assertSame(100, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testSingleQuestionWithWrongAnswerGetsZeroScore(): void
    {
        $correctAnswer = new AnswerOption(1, '4', true);
        $wrongAnswer = new AnswerOption(2, '5', false);

        $question = new Question(
            1,
            '2 + 2 = ?',
            [$correctAnswer, $wrongAnswer],
            QuestionType::SINGLE,
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(
                timeLimit: 60,
                attemptsLimit: 1,
                passingScore: 70,
                questionsCount: 10,
            ),
        );

        $attempt = new Attempt(
            1,
            $test,
            100,
            new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer($question, [$wrongAnswer]),
            new DateTimeImmutable('2026-10-01 10:31:00'),
        );

        $result = (new AttemptResultCalculator())->calculate($attempt);

        self::assertSame(1, $result->getTotalQuestions());
        self::assertSame(1, $result->getAnsweredQuestions());
        self::assertSame(0, $result->getCorrectAnswers());
        self::assertSame(0, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testMultipleQuestionWithAllCorrectAnswersGetsFullScore(): void
    {
        $answer1 = new AnswerOption(1, '2', true);
        $answer2 = new AnswerOption(2, '3', false);
        $answer3 = new AnswerOption(3, '4', true);

        $question = new Question(
            1,
            'Какие числа являются чётными?',
            [$answer1, $answer2, $answer3],
            QuestionType::MULTIPLE,
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(
                timeLimit: 60,
                attemptsLimit: 1,
                passingScore: 70,
                questionsCount: 10,
            ),
        );

        $attempt = new Attempt(
            1,
            $test,
            100,
            new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer($question, [$answer1, $answer3]),
            new DateTimeImmutable('2026-10-01 10:31:00'),
        );

        $result = (new AttemptResultCalculator())->calculate($attempt);

        self::assertSame(1, $result->getTotalQuestions());
        self::assertSame(1, $result->getAnsweredQuestions());
        self::assertSame(1, $result->getCorrectAnswers());
        self::assertSame(100, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testMultipleQuestionWithPartiallySelectedCorrectAnswersIsIncorrect(): void
    {
        $answer1 = new AnswerOption(1, '2', true);
        $answer2 = new AnswerOption(2, '3', false);
        $answer3 = new AnswerOption(3, '4', true);

        $question = new Question(
            1,
            'Какие числа являются чётными?',
            [$answer1, $answer2, $answer3],
            QuestionType::MULTIPLE,
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(
                timeLimit: 60,
                attemptsLimit: 1,
                passingScore: 70,
                questionsCount: 10,
            ),
        );

        $attempt = new Attempt(
            1,
            $test,
            100,
            new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer($question, [$answer1]),
            new DateTimeImmutable('2026-10-01 10:31:00'),
        );

        $result = (new AttemptResultCalculator())->calculate($attempt);

        self::assertSame(0, $result->getCorrectAnswers());
        self::assertSame(0, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testMultipleQuestionWithExtraWrongAnswerIsIncorrect(): void
    {
        $answer1 = new AnswerOption(1, '2', true);
        $answer2 = new AnswerOption(2, '3', false);
        $answer3 = new AnswerOption(3, '4', true);

        $question = new Question(
            1,
            'Какие числа являются чётными?',
            [$answer1, $answer2, $answer3],
            QuestionType::MULTIPLE,
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(
                timeLimit: 60,
                attemptsLimit: 1,
                passingScore: 70,
                questionsCount: 10,
            ),
        );

        $attempt = new Attempt(
            1,
            $test,
            100,
            new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer($question, [$answer1, $answer2, $answer3]),
            new DateTimeImmutable('2026-10-01 10:31:00'),
        );

        $result = (new AttemptResultCalculator())->calculate($attempt);

        self::assertSame(0, $result->getCorrectAnswers());
        self::assertSame(0, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testMultipleQuestionDoesNotDependOnAnswerOrder(): void
    {
        $answer1 = new AnswerOption(1, '2', true);
        $answer2 = new AnswerOption(2, '3', false);
        $answer3 = new AnswerOption(3, '4', true);

        $question = new Question(
            1,
            'Какие числа являются чётными?',
            [$answer1, $answer2, $answer3],
            QuestionType::MULTIPLE,
        );

        $test = new Test(
            1,
            'Математика',
            '',
            [$question],
            new TestConfiguration(
                timeLimit: 60,
                attemptsLimit: 1,
                passingScore: 70,
                questionsCount: 10,
            ),
        );

        $attempt = new Attempt(
            1,
            $test,
            100,
            new DateTimeImmutable(),
        );

        // Правильные ответы выбраны в обратном порядке.
        $attempt->answerQuestion(
            new UserAnswer($question, [$answer3, $answer1]),
            new DateTimeImmutable('2026-10-01 10:31:00'),
        );

        $result = (new AttemptResultCalculator())->calculate($attempt);

        self::assertSame(1, $result->getCorrectAnswers());
        self::assertSame(100, $result->getScore());
        self::assertTrue($result->isPassed());
    }
}
