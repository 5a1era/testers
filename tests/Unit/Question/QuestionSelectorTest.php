<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Unit\Question;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Question\QuestionSelector;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class QuestionSelectorTest extends TestCase
{
    public function testSelectsRequestedNumberOfQuestions(): void
    {
        $questions = $this->createQuestions(10);

        $selector = new QuestionSelector();

        $selected = $selector->select(questions: $questions, count: 5);

        self::assertCount(5, $selected);
    }

    public function testSelectedQuestionsBelongToOriginalCollection(): void
    {
        $questions = $this->createQuestions(10);

        $selector = new QuestionSelector();

        $selected = $selector->select(questions: $questions, count: 5);

        $originalIds = array_map(
            static fn(Question $question): int => $question->getId(),
            $questions,
        );

        foreach ($selected as $question) {
            self::assertContains(
                $question->getId(),
                $originalIds,
            );
        }
    }

    public function testSelectedQuestionsDoNotRepeat(): void
    {
        $questions = $this->createQuestions(20);

        $selector = new QuestionSelector();

        $selected = $selector->select(questions: $questions, count: 10);

        $selectedIds = array_map(
            static fn(Question $question): int => $question->getId(),
            $selected,
        );

        self::assertCount(
            count($selectedIds),
            array_unique($selectedIds),
        );
    }

    public function testCanSelectAllQuestions(): void
    {
        $questions = $this->createQuestions(5);

        $selector = new QuestionSelector();

        $selected = $selector->select(
            questions: $questions,
            count: 5,
        );

        self::assertCount(5, $selected);

        $selectedIds = array_map(
            static fn(Question $question): int => $question->getId(),
            $selected,
        );

        $originalIds = array_map(
            static fn(Question $question): int => $question->getId(),
            $questions,
        );

        sort($selectedIds);
        sort($originalIds);

        self::assertSame($originalIds, $selectedIds);
    }

    public function testThrowsExceptionWhenThereAreNotEnoughQuestions(): void
    {
        $questions = $this->createQuestions(5);

        $selector = new QuestionSelector();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'Not enough questions to select.'
        );

        $selector->select(
            questions: $questions,
            count: 6,
        );
    }

    public function testThrowsExceptionWhenCountIsZero(): void
    {
        $questions = $this->createQuestions(5);

        $selector = new QuestionSelector();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'Question count must be greater than zero.'
        );

        $selector->select(
            questions: $questions,
            count: 0,
        );
    }

    public function testThrowsExceptionWhenCountIsNegative(): void
    {
        $questions = $this->createQuestions(5);

        $selector = new QuestionSelector();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'Question count must be greater than zero.'
        );

        $selector->select(
            questions: $questions,
            count: -1,
        );
    }

    /**
     * @return Question[]
     */
    private function createQuestions(int $count): array
    {
        $questions = [];

        for ($i = 1; $i <= $count; $i++) {
            $questions[] = new Question(
                id: $i,
                text: 'Question ' . $i,
                answers: [
                    new AnswerOption(
                        id: $i,
                        text: 'Answer ' . $i,
                        correct: true,
                    ),
                ],
            );
        }

        return $questions;
    }
}
