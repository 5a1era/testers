<?php

declare(strict_types=1);

namespace Elros\Testing\Question;

enum QuestionType: string
{
    case SINGLE = 'single';
    case MULTIPLE = 'multiple';
}
