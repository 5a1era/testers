<?php

declare(strict_types=1);

namespace Elros\Testing\Question;

final class Question
{
    /**
     * @param AnswerOption[] $answers
     */
    public function __construct(
        private readonly int $id,
        private string $text,
        private array $answers,
        private QuestionType $type = QuestionType::SINGLE
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return AnswerOption[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function getType(): QuestionType
    {
        return $this->type;
    }
}
