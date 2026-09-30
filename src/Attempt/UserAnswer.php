<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;

final class UserAnswer
{
    public function __construct(
        private readonly Question $question,
        private readonly AnswerOption $answer,
    ) {}

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getAnswer(): AnswerOption
    {
        return $this->answer;
    }
}
