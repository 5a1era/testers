<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Question\QuestionType;
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
        $this->validateAnswers();
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

    private function validateAnswers(): void
    {
        if ($this->answers === []) {
            throw new LogicException(
                'At least one answer must be selected.'
            );
        }

        if (
            $this->question->getType() === QuestionType::SINGLE
            && count($this->answers) !== 1
        ) {
            throw new LogicException(
                'Single-choice question must have exactly one selected answer.'
            );
        }

        foreach ($this->answers as $answer) {
            if (!$this->hasAnswerOption($answer)) {
                throw new LogicException(
                    'Answer does not belong to this question.'
                );
            }
        }
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