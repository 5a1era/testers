<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Question;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use PHPUnit\Framework\TestCase;

final class QuestionTest extends TestCase
{
    public function testQuestionContainsAnswers(): void
    {
        $question = new Question(
            1,
            'Сколько будет 2 + 2?',
            [
                new AnswerOption(1, '3', false),
                new AnswerOption(2, '4', true),
                new AnswerOption(3, '5', false),
            ],
        );

        self::assertSame(1, $question->getId());
        self::assertSame('Сколько будет 2 + 2?', $question->getText());

        self::assertCount(3, $question->getAnswers());
        self::assertSame('4', $question->getAnswers()[1]->getText());
    }
}
