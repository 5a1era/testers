<?php

declare(strict_types=1);

namespace Elros\Testing\Attempt;

final class AttemptResultCalculator
{
    public function calculate(Attempt $attempt): AttemptResult
    {
        $questions = $attempt->getTest()->getQuestions();
        $answers = $attempt->getAnswers();

        $totalQuestions = count($questions);
        $answeredQuestions = count($answers);
        $correctAnswers = 0;

        foreach ($answers as $userAnswer) {
            if ($userAnswer->getAnswer()->isCorrect()) {
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
}
