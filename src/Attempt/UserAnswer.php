<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use LogicException;

final class UserAnswer
{
    /**
     * @param AnswerOption[] $answers
     */
    public function __construct(
        private readonly Question $question,
        private readonly array $answers,
    ) {

        foreach ($answers as $answer) {

            if (!$this->hasAnswerOption($answer)) {
                throw new LogicException(
                    'Answer does not belong to this question.'
                );
            }
        }
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }
    /**
     * @return AnswerOption[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    private function hasAnswerOption(AnswerOption $answer): bool
    {
        foreach ($this->question->getAnswers() as $questionAnswer) {
            if ($questionAnswer->getId() === $answer->getId()) {
                return true;
            }
        }

        return false;
    }
}
