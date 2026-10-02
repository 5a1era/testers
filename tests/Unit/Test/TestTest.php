<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Test;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use PHPUnit\Framework\TestCase;

final class TestTest extends TestCase
{
    public function testTestCanBeCreated(): void
    {
        $question = new Question(
            id: 1,
            text: '2 + 2 = ?',
            answers: [
                new AnswerOption(1, '3', false),
                new AnswerOption(2, '4', true),
            ],
        );

        $configuration = new TestConfiguration(
            timeLimit: 1800,
            attemptsLimit: 2,
            passingScore: 70,
            questionsCount: 1,
        );

        $test = new Test(
            id: 1,
            title: 'Математика',
            description: 'Базовый тест',
            questions: [$question],
            configuration: $configuration,
        );

        self::assertSame(1, $test->getId());
        self::assertSame('Математика', $test->getTitle());
        self::assertSame('Базовый тест', $test->getDescription());
        self::assertSame([$question], $test->getQuestions());
        self::assertSame($configuration, $test->getConfiguration());
    }

    public function testTestContainsAllQuestions(): void
    {
        $question1 = new Question(
            id: 1,
            text: 'Первый вопрос',
            answers: [],
        );

        $question2 = new Question(
            id: 2,
            text: 'Второй вопрос',
            answers: [],
        );

        $configuration = new TestConfiguration(
            timeLimit: 1800,
            attemptsLimit: 2,
            passingScore: 70,
            questionsCount: 1,
        );

        $test = new Test(
            id: 1,
            title: 'Тест',
            description: 'Описание',
            questions: [$question1, $question2],
            configuration: $configuration,
        );

        self::assertCount(2, $test->getQuestions());
        self::assertSame($question1, $test->getQuestions()[0]);
        self::assertSame($question2, $test->getQuestions()[1]);
    }
}