<?php

declare(strict_types=1);

namespace Elros\Testing\Question;

use InvalidArgumentException;

final class QuestionSelector
{
    /**
     * @param Question[] $questions
     *
     * @return Question[]
     */
    public function select(array $questions, int $count): array
    {
        if ($count <= 0) {
            throw new InvalidArgumentException(
                'Question count must be greater than zero.'
            );
        }

        if (count($questions) < $count) {
            throw new InvalidArgumentException(
                'Not enough questions to select.'
            );
        }

        shuffle($questions);

        return array_slice($questions, 0, $count);
    }
}
