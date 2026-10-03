<?php

namespace App\Support;

class Money
{
    /** Format d'affichage unique des montants : 1 250,00 $ */
    public static function usd(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, ',', "\u{202F}").' $';
    }
}
