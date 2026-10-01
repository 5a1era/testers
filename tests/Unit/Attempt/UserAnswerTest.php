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

        $userAnswer = new UserAnswer($question, $answer2);

        self::assertSame($question, $userAnswer->getQuestion());

        self::assertSame($answer2, $userAnswer->getAnswer());
    }

    public function testAnswerMustBelongToQuestion(): void
    {
        $answerFromQuestionA = new AnswerOption(1, '3', false);

        $answerFromQuestionB = new AnswerOption(2, '4', true);

        $questionA = new Question(1, 'Question A', [$answerFromQuestionA]);

        $this->expectException(LogicException::class);

        new UserAnswer($questionA, $answerFromQuestionB);
    }
}
