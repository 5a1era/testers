<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Attempt;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Attempt\AttemptResultCalculator;
use Elros\Testing\Attempt\UserAnswer;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Question\QuestionType;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AttemptResultCalculatorTest extends TestCase
{
    public function testCalculatesResultForAllCorrectAnswers(): void
    {
        $question1 = $this->createSingleQuestion(
            id: 1,
            correctAnswerId: 11,
        );

        $question2 = $this->createSingleQuestion(
            id: 2,
            correctAnswerId: 21,
        );

        $question3 = $this->createSingleQuestion(
            id: 3,
            correctAnswerId: 31,
        );

        $test = $this->createTest([
            $question1,
            $question2,
            $question3,
        ]);

        $attempt = $this->createAttempt(
            test: $test,
            questions: [
                $question1,
                $question2,
                $question3,
            ],
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                [$question2->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question3,
                [$question3->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(3, $result->getTotalQuestions());
        self::assertSame(3, $result->getAnsweredQuestions());
        self::assertSame(3, $result->getCorrectAnswers());
        self::assertSame(100, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testCalculatesResultForPartiallyCorrectAnswers(): void
    {
        $question1 = $this->createSingleQuestion(
            id: 1,
            correctAnswerId: 11,
        );

        $question2 = $this->createSingleQuestion(
            id: 2,
            correctAnswerId: 21,
        );

        $question3 = $this->createSingleQuestion(
            id: 3,
            correctAnswerId: 31,
        );

        $question4 = $this->createSingleQuestion(
            id: 4,
            correctAnswerId: 41,
        );

        $test = $this->createTest([
            $question1,
            $question2,
            $question3,
            $question4,
        ]);

        $attempt = $this->createAttempt(
            test: $test,
            questions: [
                $question1,
                $question2,
                $question3,
                $question4,
            ],
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question2,
                [$question2->getAnswers()[1]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question3,
                [$question3->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question4,
                [$question4->getAnswers()[1]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(4, $result->getTotalQuestions());
        self::assertSame(4, $result->getAnsweredQuestions());
        self::assertSame(2, $result->getCorrectAnswers());
        self::assertSame(50, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testUnansweredQuestionsAreIncludedInTotal(): void
    {
        $question1 = $this->createSingleQuestion(
            id: 1,
            correctAnswerId: 11,
        );

        $question2 = $this->createSingleQuestion(
            id: 2,
            correctAnswerId: 21,
        );

        $question3 = $this->createSingleQuestion(
            id: 3,
            correctAnswerId: 31,
        );

        $test = $this->createTest([
            $question1,
            $question2,
            $question3,
        ]);

        $attempt = $this->createAttempt(
            test: $test,
            questions: [
                $question1,
                $question2,
                $question3,
            ],
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(3, $result->getTotalQuestions());
        self::assertSame(1, $result->getAnsweredQuestions());
        self::assertSame(1, $result->getCorrectAnswers());
        self::assertSame(33, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testPassingScoreIsIncludedInPassedResult(): void
    {
        $question1 = $this->createSingleQuestion(1, 11);
        $question2 = $this->createSingleQuestion(2, 21);

        $test = $this->createTest(
            questions: [$question1, $question2],
            passingScore: 50,
        );

        $attempt = $this->createAttempt(
            test: $test,
            questions: [$question1, $question2],
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(50, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testScoreBelowPassingScoreIsNotPassed(): void
    {
        $question1 = $this->createSingleQuestion(1, 11);
        $question2 = $this->createSingleQuestion(2, 21);

        $test = $this->createTest(
            questions: [$question1, $question2],
            passingScore: 51,
        );

        $attempt = $this->createAttempt(
            test: $test,
            questions: [$question1, $question2],
        );

        $attempt->answerQuestion(
            new UserAnswer(
                $question1,
                [$question1->getAnswers()[0]],
            ),
            new DateTimeImmutable('2026-10-02 10:10:00'),
        );

        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(50, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testMultipleQuestionIsCorrectWhenAllCorrectAnswersAreSelected(): void
    {
        $question = new Question(
            id: 1,
            text: 'Select correct answers',
            answers: [
                new AnswerOption(11, 'Answer 1', true),
                new AnswerOption(12, 'Answer 2', false),
                new AnswerOption(13, 'Answer 3', true),
                new AnswerOption(14, 'Answer 4', false),
            ],
            type: QuestionType::MULTIPLE,
        );

        $userAnswer = new UserAnswer(
            $question,
            [
                $question->getAnswers()[2],
                $question->getAnswers()[0],
            ],
        );

        $calculator = new AttemptResultCalculator();

        self::assertTrue(
            $calculator->isCorrect($userAnswer),
        );
    }

    public function testMultipleQuestionIsIncorrectWhenOneCorrectAnswerIsMissing(): void
    {
        $question = new Question(
            id: 1,
            text: 'Select correct answers',
            answers: [
                new AnswerOption(11, 'Answer 1', true),
                new AnswerOption(12, 'Answer 2', false),
                new AnswerOption(13, 'Answer 3', true),
            ],
            type: QuestionType::MULTIPLE,
        );

        $userAnswer = new UserAnswer(
            $question,
            [
                $question->getAnswers()[0],
            ],
        );

        $calculator = new AttemptResultCalculator();

        self::assertFalse(
            $calculator->isCorrect($userAnswer),
        );
    }

    public function testMultipleQuestionIsIncorrectWhenExtraWrongAnswerIsSelected(): void
    {
        $question = new Question(
            id: 1,
            text: 'Select correct answers',
            answers: [
                new AnswerOption(11, 'Answer 1', true),
                new AnswerOption(12, 'Answer 2', false),
                new AnswerOption(13, 'Answer 3', true),
            ],
            type: QuestionType::MULTIPLE,
        );

        $userAnswer = new UserAnswer(
            $question,
            [
                $question->getAnswers()[0],
                $question->getAnswers()[2],
                $question->getAnswers()[1],
            ],
        );

        $calculator = new AttemptResultCalculator();

        self::assertFalse(
            $calculator->isCorrect($userAnswer),
        );
    }

    /**
     * @param Question[] $questions
     */
    private function createTest(
        array $questions,
        int $passingScore = 70,
    ): Test {
        return new Test(
            id: 1,
            title: 'Test',
            description: 'Description',
            questions: $questions,
            configuration: new TestConfiguration(
                timeLimit: 3600,
                attemptsLimit: 2,
                passingScore: $passingScore,
                questionsCount: count($questions),
            ),
        );
    }

    /**
     * @param Question[] $questions
     */
    private function createAttempt(
        Test $test,
        array $questions,
    ): Attempt {
        return new Attempt(
            id: 1,
            test: $test,
            userId: 10,
            startedAt: new DateTimeImmutable('2026-10-02 10:00:00'),
            questions: $questions,
        );
    }

    private function createSingleQuestion(
        int $id,
        int $correctAnswerId,
    ): Question {
        return new Question(
            id: $id,
            text: 'Question ' . $id,
            answers: [
                new AnswerOption(
                    id: $correctAnswerId,
                    text: 'Correct answer',
                    correct: true,
                ),
                new AnswerOption(
                    id: $correctAnswerId + 1,
                    text: 'Wrong answer',
                    correct: false,
                ),
            ],
        );
    }
}
