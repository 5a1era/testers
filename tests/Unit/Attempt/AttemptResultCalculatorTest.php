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
    public function testCalculatesResult(): void
    {
        $question1 = new Question(
            1,
            '2 + 2 = ?',
            [
                new AnswerOption(1, '3', false),
                new AnswerOption(2, '4', true),
            ],
        );

        $question2 = new Question(
            2,
            '2 + 3 = ?',
            [
                new AnswerOption(3, '5', true),
                new AnswerOption(4, '6', false),
            ],
        );

        $question3 = new Question(
            3,
            '2 + 4 = ?',
            [
                new AnswerOption(5, '5', false),
                new AnswerOption(6, '6', true),
            ],
        );

        $test = new Test(
            1,
            'Математика',
            'Базовый тест',
            [
                $question1,
                $question2,
                $question3,
            ],
            new TestConfiguration(
                timeLimit: 30,
                attemptsLimit: 3,
                passingScore: 67,
            ),
        );

        $attempt = new Attempt(
            id: 100,
            test: $test,
            userId: 500,
            startedAt: new DateTimeImmutable(),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                $question1->getAnswers()[1],
            ),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                $question2->getAnswers()[1],
            ),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question3,
                $question3->getAnswers()[1],
            ),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(3, $result->getTotalQuestions());
        self::assertSame(3, $result->getAnsweredQuestions());
        self::assertSame(2, $result->getCorrectAnswers());
        self::assertSame(67, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testTestIsNotPassedWhenScoreIsBelowPassingScore(): void
    {
        $question1 = new Question(
            1,
            'Question 1',
            [
                new AnswerOption(1, 'Correct', true),
                new AnswerOption(2, 'Wrong', false),
            ],
        );

        $question2 = new Question(
            2,
            'Question 2',
            [
                new AnswerOption(3, 'Correct', true),
                new AnswerOption(4, 'Wrong', false),
            ],
        );

        $test = new Test(
            1,
            'Test',
            '',
            [$question1, $question2],
            new TestConfiguration(
                timeLimit: 30,
                attemptsLimit: 3,
                passingScore: 70,
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
                $question1,
                $question1->getAnswers()[0],
            ),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                $question2->getAnswers()[1],
            ),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(2, $result->getTotalQuestions());
        self::assertSame(2, $result->getAnsweredQuestions());
        self::assertSame(1, $result->getCorrectAnswers());
        self::assertSame(50, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testUnansweredQuestionsDoNotIncreaseScore(): void
    {
        $questions = [
            new Question(
                1,
                'Question 1',
                [
                    new AnswerOption(1, 'Correct', true),
                    new AnswerOption(2, 'Wrong', false),
                ],
            ),
            new Question(
                2,
                'Question 2',
                [
                    new AnswerOption(3, 'Correct', true),
                    new AnswerOption(4, 'Wrong', false),
                ],
            ),
            new Question(
                3,
                'Question 3',
                [
                    new AnswerOption(5, 'Correct', true),
                    new AnswerOption(6, 'Wrong', false),
                ],
            ),
            new Question(
                4,
                'Question 4',
                [
                    new AnswerOption(7, 'Correct', true),
                    new AnswerOption(8, 'Wrong', false),
                ],
            ),
            new Question(
                5,
                'Question 5',
                [
                    new AnswerOption(9, 'Correct', true),
                    new AnswerOption(10, 'Wrong', false),
                ],
            ),
        ];

        $test = new Test(
            1,
            'Test',
            '',
            $questions,
            new TestConfiguration(
                timeLimit: 30,
                attemptsLimit: 3,
                passingScore: 70,
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
                $questions[0],
                $questions[0]->getAnswers()[0],
            ),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $questions[1],
                $questions[1]->getAnswers()[0],
            ),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $questions[2],
                $questions[2]->getAnswers()[1],
            ),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(5, $result->getTotalQuestions());
        self::assertSame(3, $result->getAnsweredQuestions());
        self::assertSame(2, $result->getCorrectAnswers());
        self::assertSame(40, $result->getScore());
        self::assertFalse($result->isPassed());
    }
}
