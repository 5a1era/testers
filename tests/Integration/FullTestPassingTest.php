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

final class FullTestPassingTest extends TestCase
{
    public function testUserCanCompleteTestAndGetResult(): void
    {

        $q1Answer1 = new AnswerOption(1, 'Москва', true);
        $q1Answer2 = new AnswerOption(2, 'Санкт-Петербург', false);
        $q1Answer3 = new AnswerOption(3, 'Казань', false);

        $question1 = new Question(
            1,
            'Столица России?',
            [$q1Answer1, $q1Answer2, $q1Answer3],
            QuestionType::SINGLE,
        );

        $q2Answer1 = new AnswerOption(4, '2', false);
        $q2Answer2 = new AnswerOption(5, '4', true);
        $q2Answer3 = new AnswerOption(6, '6', false);

        $question2 = new Question(
            2,
            'Сколько будет 2 + 2?',
            [$q2Answer1, $q2Answer2, $q2Answer3],
            QuestionType::SINGLE,
        );

        $q3Answer1 = new AnswerOption(7, 'PHP', true);
        $q3Answer2 = new AnswerOption(8, 'HTML', false);
        $q3Answer3 = new AnswerOption(9, 'CSS', false);

        $question3 = new Question(
            3,
            'Какой язык используется для серверной разработки?',
            [$q3Answer1, $q3Answer2, $q3Answer3],
            QuestionType::SINGLE,
        );


        $q4Answer1 = new AnswerOption(10, '2', true);
        $q4Answer2 = new AnswerOption(11, '3', false);
        $q4Answer3 = new AnswerOption(12, '4', true);
        $q4Answer4 = new AnswerOption(13, '5', false);

        $question4 = new Question(
            4,
            'Какие числа являются чётными?',
            [$q4Answer1, $q4Answer2, $q4Answer3, $q4Answer4],
            QuestionType::MULTIPLE,
        );

        $q5Answer1 = new AnswerOption(14, 'GET', false);
        $q5Answer2 = new AnswerOption(15, 'POST', true);
        $q5Answer3 = new AnswerOption(16, 'DELETE', false);

        $question5 = new Question(
            5,
            'Какой HTTP-метод обычно используется для создания ресурса?',
            [$q5Answer1, $q5Answer2, $q5Answer3],
            QuestionType::SINGLE,
        );

        $configuration = new TestConfiguration(
            timeLimit: 30 * 60,
            attemptsLimit: 2,
            passingScore: 80,
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
            configuration: $configuration,
        );

        /*
         * 1. Пользователь начинает прохождение
         */

        $attempt = new Attempt(
            id: 1,
            test: $test,
            userId: 100,
            startedAt: new DateTimeImmutable(),
        );

        /*
         * 2. Пользователь отвечает на вопросы
         */

        // Вопрос 1 — правильно
        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$q1Answer1]
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        // Вопрос 2 — правильно
        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                [$q2Answer2]
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        // Вопрос 3 — неправильно
        $attempt->answerQuestion(
            new UserAnswer(
                $question3,
                [$q3Answer2]
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        // Вопрос 4 — MULTIPLE, оба правильных ответа
        $attempt->answerQuestion(
            new UserAnswer(
                $question4,
                [$q4Answer1, $q4Answer3]
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        // Вопрос 5 — неправильно
        $attempt->answerQuestion(
            new UserAnswer(
                $question5,
                [$q5Answer1]
            ),
            new DateTimeImmutable('2026-10-01 10:10:00')
        );

        /*
         * 3. Пользователь завершает прохождение
         */

        $attempt->finish(new DateTimeImmutable());

        /*
         * 4. Рассчитываем результат
         */

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(5, $result->getTotalQuestions());
        self::assertSame(5, $result->getAnsweredQuestions());
        self::assertSame(3, $result->getCorrectAnswers());
        self::assertSame(60, $result->getScore());
        self::assertFalse($result->isPassed());
    }
}
