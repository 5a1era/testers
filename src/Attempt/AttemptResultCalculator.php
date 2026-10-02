<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

final class AttemptResultCalculator
{
    public function calculate(Attempt $attempt): AttemptResult
    {
        $questions = $attempt->getQuestions();
        $answers = $attempt->getAnswers();

        $totalQuestions = count($questions);
        $answeredQuestions = count($answers);
        $correctAnswers = 0;

        foreach ($answers as $userAnswer) {
            if ($this->isCorrect($userAnswer)) {
                $correctAnswers++;
            }
        }

        $score = $totalQuestions === 0
            ? 0
            : (int) round(
                $correctAnswers / $totalQuestions * 100
            );

        $passed = $score >= $attempt
            ->getTest()
            ->getConfiguration()
            ->getPassingScore();

        return new AttemptResult(
            totalQuestions: $totalQuestions,
            answeredQuestions: $answeredQuestions,
            correctAnswers: $correctAnswers,
            score: $score,
            passed: $passed,
        );
    }

    public function isCorrect(UserAnswer $userAnswer): bool
    {
        $correctAnswerIds = [];

        foreach ($userAnswer->getQuestion()->getAnswers() as $answer) {
            if ($answer->isCorrect()) {
                $correctAnswerIds[] = $answer->getId();
            }
        }

        $selectedAnswerIds = [];

        foreach ($userAnswer->getAnswers() as $answer) {
            $selectedAnswerIds[] = $answer->getId();
        }

        sort($correctAnswerIds);
        sort($selectedAnswerIds);

        return $correctAnswerIds === $selectedAnswerIds;
    }
}
