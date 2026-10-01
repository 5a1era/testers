<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Attempt;

use Elros\Testing\Attempt\UserAnswer;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use LogicException;
use PHPUnit\Framework\TestCase;

final class UserAnswerTest extends TestCase
{
    public function testUserAnswerContainsQuestionAndAnswer(): void
    {
        $answer1 = new AnswerOption(1, '3', false);

        $answer2 = new AnswerOption(2, '4', true);

        $question = new Question(1, 'Сколько будет 2 + 2?', [$answer1, $answer2]);

        $userAnswer = new UserAnswer($question, [$answer2]);

        self::assertSame($question, $userAnswer->getQuestion());

        self::assertSame([$answer2], $userAnswer->getAnswers());
    }

    public function testAnswerMustBelongToQuestion(): void
    {
        $answerFromQuestionA = new AnswerOption(1, '3', false);

        $answerFromQuestionB = new AnswerOption(2, '4', true);

        $questionA = new Question(1, 'Question A', [$answerFromQuestionA]);

        $this->expectException(LogicException::class);

        new UserAnswer($questionA, [$answerFromQuestionB]);
    }

    public function testCanStoreMultipleSelectedAnswers(): void
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

        $userAnswer = new UserAnswer($question, [$answer1, $answer3]);

        self::assertSame([$answer1, $answer3], $userAnswer->getAnswers());
    }

    public function testAllSelectedAnswersMustBelongToQuestion(): void
    {
        $questionAnswer = new AnswerOption(1, 'Да', true);

        $foreignAnswer = new AnswerOption(2, 'Нет', false);

        $question = new Question(1, 'Вопрос', [$questionAnswer]);

        $this->expectException(LogicException::class);

        new UserAnswer($question, [$questionAnswer, $foreignAnswer]);
    }
}
