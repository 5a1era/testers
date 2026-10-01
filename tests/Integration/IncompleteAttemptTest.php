<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Integration\Attempt;

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

final class IncompleteAttemptTest extends TestCase
{
    public function testIncompleteAttempt(): void
    {
        $question1 = new Question(
            1,
            'Столица России?',
            [
                new AnswerOption(1, 'Москва', true),
                new AnswerOption(2, 'Санкт-Петербург', false),
            ],
        );

        $question2 = new Question(
            2,
            'Сколько будет 2 + 2?',
            [
                new AnswerOption(3, '3', false),
                new AnswerOption(4, '4', true),
            ],
        );

        $question3 = new Question(
            3,
            'Какой язык используется для серверной разработки?',
            [
                new AnswerOption(5, 'PHP', true),
                new AnswerOption(6, 'HTML', false),
            ],
        );

        $question4 = new Question(
            4,
            'Какие числа являются чётными?',
            [
                new AnswerOption(7, '2', true),
                new AnswerOption(8, '3', false),
                new AnswerOption(9, '4', true),
            ],
            QuestionType::MULTIPLE,
        );

        $question5 = new Question(
            5,
            'Какой HTTP-метод обычно используется для создания ресурса?',
            [
                new AnswerOption(10, 'GET', false),
                new AnswerOption(11, 'POST', true),
            ],
        );

        $test = new Test(
            id: 1,
            title: 'Основы программирования',
            description: 'Проверка базовых знаний',
            questions: [
                $question1,
                $question2,
                $question3,
                $question4,
                $question5,
            ],
            configuration: new TestConfiguration(
                timeLimit: 30 * 60,
                attemptsLimit: 2,
                passingScore: 70,
            ),
        );

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable(),
        );

        // Пользователь ответил только на 3 вопроса.
        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                [$question2->getAnswers()[1]],
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question4,
                [
                    $question4->getAnswers()[0],
                    $question4->getAnswers()[2],
                ],
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        $calculator = new AttemptResultCalculator();
        $result = $calculator->calculate($attempt);

        self::assertSame(5, $result->getTotalQuestions());
        self::assertSame(3, $result->getAnsweredQuestions());
        self::assertSame(3, $result->getCorrectAnswers());
        self::assertSame(60, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testAttemptExpiresAfterTimeLimit(): void
    {
        $test = new Test(
            id: 1,
            title: 'Test',
            description: 'Test',
            questions: [],
            configuration: new TestConfiguration(
                timeLimit: 30 * 60,
                attemptsLimit: 2,
                passingScore: 70,
            ),
        );

        $startedAt = new DateTimeImmutable('2026-10-01 10:00:00');

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
        );

        self::assertFalse(
            $attempt->isExpired(
                new DateTimeImmutable('2026-10-01 10:29:59')
            )
        );

        self::assertTrue(
            $attempt->isExpired(
                new DateTimeImmutable('2026-10-01 10:30:00')
            )
        );

        self::assertTrue(
            $attempt->isExpired(
                new DateTimeImmutable('2026-10-01 10:31:00')
            )
        );
    }
}
