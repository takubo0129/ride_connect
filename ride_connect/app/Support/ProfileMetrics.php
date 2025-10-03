<?php

namespace App\Support;

class ProfileMetrics
{
    public const HEIGHT_UNDER_VALUE = 139;
    public const HEIGHT_OVER_VALUE = 190;
    public const WEIGHT_UNDER_VALUE = 39;
    public const WEIGHT_OVER_VALUE = 120;

    /**
     * @return array<int>
     */
    public static function heightOptionValues(): array
    {
        return array_merge([self::HEIGHT_UNDER_VALUE], range(140, 189), [self::HEIGHT_OVER_VALUE]);
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function heightOptions(): array
    {
        return array_map(fn (int $value) => [
            'value' => $value,
            'label' => self::heightLabel($value),
        ], self::heightOptionValues());
    }

    /**
     * @return array<int>
     */
    public static function weightOptionValues(): array
    {
        return array_merge([self::WEIGHT_UNDER_VALUE], range(40, 119), [self::WEIGHT_OVER_VALUE]);
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function weightOptions(): array
    {
        return array_map(fn (int $value) => [
            'value' => $value,
            'label' => self::weightLabel($value),
        ], self::weightOptionValues());
    }

    public static function heightLabel(int $value): string
    {
        return match (true) {
            $value === self::HEIGHT_UNDER_VALUE => '140cm未満',
            $value === self::HEIGHT_OVER_VALUE => '190cm以上',
            default => $value . 'cm',
        };
    }

    public static function weightLabel(int $value): string
    {
        return match (true) {
            $value === self::WEIGHT_UNDER_VALUE => '40kg未満',
            $value === self::WEIGHT_OVER_VALUE => '120kg以上',
            default => $value . 'kg',
        };
    }
}
