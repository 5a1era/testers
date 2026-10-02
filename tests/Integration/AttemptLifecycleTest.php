<?php

declare(strict_types=1);

namespace Elros\Testing\Tests\Integration;

use Elros\Testing\Attempt\Attempt;
use Elros\Testing\Attempt\AttemptManager;
use Elros\Testing\Attempt\AttemptResultCalculator;
use Elros\Testing\Attempt\UserAnswer;
use Elros\Testing\Question\AnswerOption;
use Elros\Testing\Question\Question;
use Elros\Testing\Question\QuestionSelector;
use Elros\Testing\Question\QuestionType;
use Elros\Testing\Test\Test;
use Elros\Testing\Test\TestConfiguration;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;

final class AttemptLifecycleTest extends TestCase
{
    public function testCompleteAttemptLifecycle(): void
    {
        $questions = [
            $this->createQuestion(
                id: 1,
                correctAnswerId: 101,
            ),
            $this->createQuestion(
                id: 2,
                correctAnswerId: 201,
            ),
            $this->createQuestion(
                id: 3,
                correctAnswerId: 301,
            ),
        ];

        $configuration = new TestConfiguration(
            timeLimit: 600,
            attemptsLimit: 2,
            passingScore: 67,
            questionsCount: 3,
        );

        $test = new Test(
            id: 1,
            title: 'PHP Test',
            description: 'Test description',
            questions: $questions,
            configuration: $configuration,
        );

        $startedAt = new DateTimeImmutable('2026-10-02 10:00:00');

        /*
         * 1. Start attempt.
         */
        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $attempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [],
            startedAt: $startedAt,
        );

        self::assertSame(1, $attempt->getId());
        self::assertSame(10, $attempt->getUserId());
        self::assertFalse($attempt->isFinished());
        self::assertCount(3, $attempt->getQuestions());

        /*
         * 2. Answer questions.
         *
         * First two answers are correct.
         * Third question remains unanswered.
         */
        $question1 = $this->findQuestion(
            $attempt,
            1,
        );

        $question2 = $this->findQuestion(
            $attempt,
            2,
        );

        $attempt->answerQuestion(
            new UserAnswer(
                question: $question1,
                answers: [$this->findAnswer($question1, 101)],
            ),
            now: new DateTimeImmutable('2026-10-02 10:01:00'),
        );

        $attempt->answerQuestion(
            new UserAnswer(
                question: $question2,
                answers: [$this->findAnswer($question2, 201)],
            ),
            now: new DateTimeImmutable('2026-10-02 10:02:00'),
        );

        self::assertCount(2, $attempt->getAnswers());
        self::assertFalse($attempt->isFinished());

        /*
         * 3. Finish attempt.
         */
        $finishedAt = new DateTimeImmutable('2026-10-02 10:03:00');

        $attempt->finish($finishedAt);

        self::assertTrue($attempt->isFinished());
        self::assertEquals(
            $finishedAt,
            $attempt->getFinishedAt(),
        );

        /*
         * 4. Calculate result.
         */
        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(3, $result->getTotalQuestions());
        self::assertSame(2, $result->getAnsweredQuestions());
        self::assertSame(2, $result->getCorrectAnswers());
        self::assertSame(67, $result->getScore());
        self::assertTrue($result->isPassed());
    }

    public function testAttemptIsFinishedWhenTimeExpires(): void
    {
        $questions = [
            $this->createQuestion(
                id: 1,
                correctAnswerId: 101,
            ),
            $this->createQuestion(
                id: 2,
                correctAnswerId: 201,
            ),
            $this->createQuestion(
                id: 3,
                correctAnswerId: 301,
            ),
        ];

        $configuration = new TestConfiguration(
            timeLimit: 60,
            attemptsLimit: 2,
            passingScore: 67,
            questionsCount: 3,
        );

        $test = new Test(
            id: 1,
            title: 'PHP Test',
            description: 'Test description',
            questions: $questions,
            configuration: $configuration,
        );

        $startedAt = new DateTimeImmutable(
            '2026-10-02 10:00:00',
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        /*
     * 1. Start attempt.
     */
        $attempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [],
            startedAt: $startedAt,
        );

        self::assertFalse($attempt->isFinished());

        /*
     * 2. Answer one question before expiration.
     */
        $question = $this->findQuestion(
            $attempt,
            1,
        );

        $attempt->answerQuestion(
            new UserAnswer(
                question: $question,
                answers: [$this->findAnswer($question, 101)],
            ),
            now: new DateTimeImmutable(
                '2026-10-02 10:00:30',
            ),
        );

        self::assertCount(1, $attempt->getAnswers());

        /*
     * 3. Time expires.
     */
        $expiredAt = new DateTimeImmutable(
            '2026-10-02 10:01:00',
        );

        self::assertTrue(
            $attempt->isExpired($expiredAt),
        );

        /*
     * 4. Finish expired attempt.
     */
        self::assertTrue(
            $attempt->finishIfExpired($expiredAt),
        );

        self::assertTrue($attempt->isFinished());

        self::assertEquals(
            $expiredAt,
            $attempt->getFinishedAt(),
        );

        /*
     * 5. Calculate result.
     */
        $calculator = new AttemptResultCalculator();

        $result = $calculator->calculate($attempt);

        self::assertSame(3, $result->getTotalQuestions());
        self::assertSame(1, $result->getAnsweredQuestions());
        self::assertSame(1, $result->getCorrectAnswers());
        self::assertSame(33, $result->getScore());
        self::assertFalse($result->isPassed());
    }

    public function testExpiredAttemptCannotBeContinued(): void
    {
        $questions = [
            $this->createQuestion(
                id: 1,
                correctAnswerId: 101,
            ),
            $this->createQuestion(
                id: 2,
                correctAnswerId: 201,
            ),
        ];

        $configuration = new TestConfiguration(
            timeLimit: 60,
            attemptsLimit: 2,
            passingScore: 50,
            questionsCount: 2,
        );

        $test = new Test(
            id: 1,
            title: 'PHP Test',
            description: 'Test description',
            questions: $questions,
            configuration: $configuration,
        );

        $startedAt = new DateTimeImmutable(
            '2026-10-02 10:00:00',
        );

        $manager = new AttemptManager(
            new QuestionSelector(),
        );

        $attempt = $manager->start(
            test: $test,
            userId: 10,
            attempts: [],
            startedAt: $startedAt,
        );

        /*
     * Время истекло.
     */
        $expiredAt = new DateTimeImmutable(
            '2026-10-02 10:01:00',
        );

        self::assertTrue(
            $attempt->finishIfExpired($expiredAt),
        );

        self::assertTrue($attempt->isFinished());

        /*
     * Попытка завершена и больше не может принимать ответы.
     */
        $question = $this->findQuestion(
            $attempt,
            1,
        );

        $answer = new UserAnswer(
            question: $question,
            answers: [$this->findAnswer($question, 101)],
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIsOrContains(
            'Cannot answer a finished attempt.',
        );

        $attempt->answerQuestion(
            userAnswer: $answer,
            now: $expiredAt,
        );
    }

    private function createQuestion(
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
            type: QuestionType::SINGLE,
        );
    }

    private function findQuestion(
        Attempt $attempt,
        int $questionId,
    ): Question {
        foreach ($attempt->getQuestions() as $question) {
            if ($question->getId() === $questionId) {
                return $question;
            }
        }

        self::fail(
            'Question ' . $questionId . ' was not found in attempt.',
        );
    }

    private function findAnswer(
        Question $question,
        int $answerId,
    ): AnswerOption {
        foreach ($question->getAnswers() as $answer) {
            if ($answer->getId() === $answerId) {
                return $answer;
            }
        }

        self::fail(
            'Answer ' . $answerId . ' was not found.',
        );
    }
}
