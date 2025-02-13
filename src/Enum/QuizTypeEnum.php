<?php

namespace App\Enum;

enum QuizTypeEnum: string
{
    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case IRRELEVANT_ANSWER = 'irrelevant_answer';

    public static function choices(): array
    {
        return [
            'Single Choice' => self::SINGLE_CHOICE,
            'Multiple Choice' => self::MULTIPLE_CHOICE,
            'Choose the Irrelevant Answer' => self::IRRELEVANT_ANSWER,
        ];
    }
}
