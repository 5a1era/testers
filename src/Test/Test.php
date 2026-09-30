<?php

declare(strict_types=1);

namespace Elros\Testing\Test;

use Elros\Testing\Question\Question;

final class Test
{
    /**
     * @param Question[] $questions
     */
    public function __construct(
        private readonly int $id,
        private string $title,
        private string $description,
        private array $questions,
        private TestConfiguration $configuration
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Question[]
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function getConfiguration(): TestConfiguration
    {
        return $this->configuration;
    }
}
