<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Question;

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

        $selected = $selector->select($questions, 5);

        self::assertCount(5, $selected);
    }

    public function testSelectedQuestionsBelongToSourceCollection(): void
    {
        $questions = $this->createQuestions(10);

        $selector = new QuestionSelector();

        $selected = $selector->select($questions, 5);

        foreach ($selected as $question) {
            self::assertContains($question, $questions);
        }
    }

    public function testDoesNotSelectDuplicateQuestions(): void
    {
        $questions = $this->createQuestions(10);

        $selector = new QuestionSelector();

        $selected = $selector->select($questions, 5);

        $ids = array_map(
            static fn(Question $question): int => $question->getId(),
            $selected,
        );

        self::assertCount(5, array_unique($ids));
    }

    public function testCanSelectAllQuestions(): void
    {
        $questions = $this->createQuestions(5);

        $selector = new QuestionSelector();

        $selected = $selector->select($questions, 5);

        self::assertCount(5, $selected);

        $selectedIds = array_map(
            static fn(Question $question): int => $question->getId(),
            $selected,
        );

        sort($selectedIds);

        self::assertSame([1, 2, 3, 4, 5], $selectedIds);
    }

    public function testCannotSelectZeroQuestions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $selector = new QuestionSelector();

        $selector->select(
            $this->createQuestions(10),
            0,
        );
    }

    public function testCannotSelectNegativeNumberOfQuestions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $selector = new QuestionSelector();

        $selector->select(
            $this->createQuestions(10),
            -1,
        );
    }

    public function testCannotSelectMoreQuestionsThanAvailable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $selector = new QuestionSelector();

        $selector->select(
            $this->createQuestions(5),
            6,
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
                answers: [],
            );
        }

        return $questions;
    }
}
