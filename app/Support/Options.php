<?php

namespace App\Support;

/** Shared dropdown values and helpers. */
class Options
{
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public static function make(array $values): array
    {
        return array_combine($values, $values);
    }

    public static function strands(): array
    {
        return [
            'ASSH' => 'Arts, Social Sciences, and Humanities (ASSH)',
            'STEM' => 'Science, Technology, Engineering, and Mathematics (STEM)',
            'B&E' => 'Business and Entrepreneurship (B&E)',
            'SHW' => 'Sports, Health, and Wellness (SHW)',
            'ASET' => 'Automotive and Small Engine Technologies (ASET)',
            'CBT' => 'Construction and Building Technologies (CBT)',
            'ABM' => 'ABM (legacy)',
            'HUMSS' => 'HUMSS (legacy)',
            'GAS' => 'GAS (legacy)',
            'TVL' => 'TVL (legacy)',
        ];
    }

    public static function days(): array
    {
        return self::make(self::DAYS);
    }

    public static function semesters(): array
    {
        return [
            '1st' => 'First Term',
            '2nd' => 'Second Term',
            '3rd' => 'Third Term',
        ];
    }

    public static function schoolYear(): string
    {
        $y = (int) date('Y');
        return ((int) date('n') >= 6) ? "{$y}-".($y + 1) : ($y - 1)."-{$y}";
    }
}
