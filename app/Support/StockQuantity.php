<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class StockQuantity
{
    public const MAX_MINOR = 999999999999; // DECIMAL(12,2): 9,999,999,999.99

    public static function minor($value, bool $signed = false): int
    {
        $text = (string) $value;
        $pattern = $signed ? '/^-?\d{1,10}(?:\.\d{1,2})?$/D' : '/^\d{1,10}(?:\.\d{1,2})?$/D';
        if (PHP_INT_SIZE < 8 || !preg_match($pattern, $text)) {
            throw ValidationException::withMessages(['amount' => 'Use a valid stock quantity with at most two decimal places.']);
        }
        $negative = str_starts_with($text, '-');
        $parts = explode('.', ltrim($text, '-'));
        $minor = ((int) $parts[0]) * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
        if ($minor > self::MAX_MINOR) {
            throw ValidationException::withMessages(['amount' => 'Quantity exceeds the stock column limit.']);
        }
        return $negative ? -$minor : $minor;
    }

    public static function decimal(int $minor): string
    {
        $abs = abs($minor);
        return ($minor < 0 ? '-' : '').intdiv($abs, 100).'.'.str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }
}
