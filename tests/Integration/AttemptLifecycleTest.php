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
use LogicException;
use PHPUnit\Framework\TestCase;

final class AttemptLifecycleTest extends TestCase
{
    public function testFullAttemptLifecycle(): void
    {
        /*
         * 1. Создаём вопросы.
         */

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
            'Какой HTTP-метод используется для создания ресурса?',
            [
                new AnswerOption(10, 'GET', false),
                new AnswerOption(11, 'POST', true),
            ],
        );

        /*
         * 2. Создаём тест.
         *
         * Лимит времени — 30 секунд.
         * Для прохождения нужно набрать 70%.
         */

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
                timeLimit: 30,
                attemptsLimit: 2,
                passingScore: 70,
                questionsCount: 10
            ),
        );

        /*
         * 3. Пользователь начинает попытку.
         */

        $startedAt = new DateTimeImmutable(
            '2026-10-01 10:00:00'
        );

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
        );

        self::assertFalse($attempt->isFinished());
        self::assertFalse(
            $attempt->isExpired($startedAt)
        );

        /*
         * 4. Пользователь отвечает на вопросы.
         *
         * Q1 — правильно.
         * Q2 — правильно.
         * Q3 — неправильно.
         * Q4 — правильно.
         * Q5 — не успел ответить.
         */

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-01 10:00:05'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                [$question2->getAnswers()[1]],
            ),
            new DateTimeImmutable('2026-10-01 10:00:10'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question3,
                [$question3->getAnswers()[1]],
            ),
            new DateTimeImmutable('2026-10-01 10:00:15'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question4,
                [
                    $question4->getAnswers()[0],
                    $question4->getAnswers()[2],
                ],
            ),
            new DateTimeImmutable('2026-10-01 10:00:20'),
        );

        self::assertCount(4, $attempt->getAnswers());

        /*
         * 5. Время истекло.
         */

        $expiredAt = new DateTimeImmutable(
            '2026-10-01 10:00:30'
        );

        self::assertTrue(
            $attempt->isExpired($expiredAt)
        );

        /*
         * 6. Автоматически завершаем попытку.
         */

        $finished = $attempt->finishIfExpired($expiredAt);

        self::assertTrue($finished);
        self::assertTrue($attempt->isFinished());

        self::assertEquals(
            $expiredAt,
            $attempt->getFinishedAt()
        );

        /*
         * 7. После окончания времени ответить нельзя.
         */

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains(
            'Cannot answer a finished attempt.'
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question5,
                [$question5->getAnswers()[1]],
            ),
            new DateTimeImmutable('2026-10-01 10:00:31'),
        );
    }

    public function testResultCanBeCalculatedAfterTimeout(): void
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

        $test = new Test(
            id: 1,
            title: 'Основы программирования',
            description: 'Проверка базовых знаний',
            questions: [
                $question1,
                $question2,
                $question3,
            ],
            configuration: new TestConfiguration(
                timeLimit: 30,
                attemptsLimit: 2,
                passingScore: 70,
                questionsCount: 10,
            ),
        );

        $startedAt = new DateTimeImmutable(
            '2026-10-01 10:00:00'
        );

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: $startedAt,
        );

        /*
         * Ответили только на два вопроса.
         *
         * Q1 — правильно.
         * Q2 — неправильно.
         * Q3 — пропущен.
         */

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-01 10:00:05'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                [$question2->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-01 10:00:10'),
        );

        $expiredAt = new DateTimeImmutable(
            '2026-10-01 10:00:30'
        );

        $attempt->finishIfExpired($expiredAt);

        /*
         * Рассчитываем результат уже завершённой попытки.
         */

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(3, $result->getTotalQuestions());
        self::assertSame(2, $result->getAnsweredQuestions());
        self::assertSame(1, $result->getCorrectAnswers());

        // 1 правильный ответ из 3 вопросов = 33%.
        self::assertSame(33, $result->getScore());

        self::assertFalse($result->isPassed());
    }
}
