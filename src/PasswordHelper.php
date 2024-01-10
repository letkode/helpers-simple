<?php

namespace Letkode\Helpers;

class PasswordHelper
{
    public static function isValidStrength(string $password): bool
    {
        $patterUpper = '/[A-Z]/';
        $patterLower = '/[a-z]/';
        $patterNumber = '/\d/';
        $patternSpecialChar = '/[!@#$%^&*()_+\-=\[\]{};\':"|,.<>\/?]/';

        $invalid['length'] = strlen($password) < 8;
        $invalid['upper'] = !preg_match($patterUpper, $password);
        $invalid['lower'] = !preg_match($patterLower, $password);
        $invalid['number'] = !preg_match($patterNumber, $password);
        $invalid['specialChar'] = !preg_match($patternSpecialChar, $password);

        return empty(array_filter($invalid));
    }

    public static function generatorPasswordRandom($length, $includeSymbol = false): string {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $symbol = '!@#$%^&*()_+-={}[]|:;"<>,.?/~`';

        if ($includeSymbol) {
            $chars .= $symbol;
        }

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $randomChar = $chars[rand(0, strlen($chars) - 1)];
            $password .= $randomChar;
        }

        return $password;
    }
}
