<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use LogicException;

final class UserAnswer
{
    public function __construct(
        private readonly Question $question,
        private readonly AnswerOption $answer,
    ) {
        if (!$this->hasAnswerOption($answer)) {
            throw new LogicException(
                'Answer does not belong to this question.'
            );
        }
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getAnswer(): AnswerOption
    {
        return $this->answer;
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
