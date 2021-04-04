<?php

namespace App\Service;

class StringConverter
{
    public function capitalize(string $value): string
    {
        return mb_convert_case(mb_substr($value, 0, 1), MB_CASE_UPPER, 'UTF-8')
        . mb_convert_case(mb_substr($value, 1), MB_CASE_LOWER, 'UTF-8');
    }
}