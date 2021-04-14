<?php

namespace App\Service;

class StringConverter
{
    public function capitalize(string $value): string
    {
        return mb_convert_case(mb_substr($value, 0, 1), MB_CASE_UPPER, 'UTF-8')
        . mb_convert_case(mb_substr($value, 1), MB_CASE_LOWER, 'UTF-8');
    }

    public function createAbbreviatedName(
        string $firstName,
        string $lastName,
        string $patronymic,
        $post = '',
        $abbrPostNameLength = 0
    ) {
        return $post && $abbrPostNameLength > 0 ? mb_substr($post, 0, $abbrPostNameLength) . '. ' : ''
            . $lastName . ' '
            . mb_substr($firstName, 0, 1) . '. '
            . $patronymic ? mb_substr($patronymic, 0, 1) . '.' : '';
    }
}