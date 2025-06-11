<?php

namespace App\Helpers;

use NumberToWords\NumberToWords;

class CurrencyHelper
{
    public static function convertAmountToWords($amount, $currency = 'INR', $locale = 'en')
    {
        $amount = number_format((float)$amount, 2, '.', '');
        [$whole, $decimal] = explode('.', $amount);

        $whole = (int) $whole;
        $decimal = (int) $decimal;

        // Handle INR with Lakhs/Crores manually
        if (strtoupper($currency) === 'INR') {
            $words = self::convertToIndianWords($whole);
            $currencyWord = 'rupees';
            $decimalWord = 'paise';
        } else {
            // Other currencies use NumberToWords package
            $numberToWords = new NumberToWords();
            $numberTransformer = $numberToWords->getNumberTransformer($locale);

            $words = ucfirst($numberTransformer->toWords($whole));
            $currencyWord = match (strtoupper($currency)) {
                'USD' => 'dollars',
                'EUR' => 'euros',
                'GBP' => 'pounds',
                default => strtolower($currency)
            };
            $decimalWord = match (strtoupper($currency)) {
                'USD' => 'cents',
                'EUR' => 'centimes',
                'GBP' => 'pence',
                default => 'cents'
            };
        }

        $decimalWords = $decimal > 0
            ? ' and ' . self::convertToIndianWords($decimal) . " $decimalWord"
            : '';

        return ucfirst($words . ' ' . $currencyWord . $decimalWords . ' only');
    }

    // 💡 Indian numbering system converter
    private static function convertToIndianWords($number)
    {
        $words = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven',
            'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen',
            'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen',
            'nineteen'
        ];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty',
            'sixty', 'seventy', 'eighty', 'ninety'];

        $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];

        if ($number == 0) {
            return 'zero';
        }

        $str = [];
        $num = (string) $number;

        $levels = [
            [10000000, 'crore'],
            [100000, 'lakh'],
            [1000, 'thousand'],
            [100, 'hundred']
        ];

        foreach ($levels as [$divisor, $name]) {
            if ($number >= $divisor) {
                $count = (int) ($number / $divisor);
                $number %= $divisor;

                $str[] = self::convertToIndianWords($count) . ' ' . $name;
            }
        }

        if ($number > 0) {
            if ($number < 20) {
                $str[] = $words[$number];
            } else {
                $str[] = $tens[(int) ($number / 10)] . ' ' . $words[$number % 10];
            }
        }

        return implode(' ', array_filter($str));
    }
}
