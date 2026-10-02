<?php
namespace App\Support;

final class InventoryQuantity
{
    // Exact hundredths; supports aggregated stock up to DECIMAL(18,2).
    public static function minor($value): int
    {
        $text = (string) ($value ?? '0');
        if (PHP_INT_SIZE < 8 || !preg_match('/^-?\d{1,16}(?:\.\d{1,2})?$/D', $text)) {
            throw new \InvalidArgumentException('Invalid inventory decimal quantity.');
        }
        $negative = str_starts_with($text, '-');
        $parts = explode('.', ltrim($text, '-'));
        $n = (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
        return $negative ? -$n : $n;
    }
    public static function decimal(int $n): string
    {
        return ($n < 0 ? '-' : '').intdiv(abs($n), 100).'.'.str_pad((string) (abs($n) % 100), 2, '0', STR_PAD_LEFT);
    }
    public static function display($value): string
    {
        $s = self::decimal(self::minor($value));
        [$whole, $frac] = explode('.', $s);
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole);
        $frac = rtrim($frac, '0');
        return $whole.($frac !== '' ? '.'.$frac : '');
    }
    public static function packs($quantity, $factor, ?string $unit): string
    {
        if (!$unit || $factor === null || self::minor($factor) <= 0) return 'Not configured';
        $q = self::minor($quantity); $f = self::minor($factor);
        // A negative legacy total is not a physically usable pack count.
        if ($q < 0) return 'Check negative stock';
        $whole = intdiv($q, $f); $remainder = $q % $f;
        return preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', (string) $whole).' '.$unit.' + '.self::display(self::decimal($remainder)).' pcs';
    }
}
