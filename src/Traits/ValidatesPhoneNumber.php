<?php

namespace RmdMostakim\BdSms\Traits;

use InvalidArgumentException;

trait ValidatesPhoneNumber
{
    /**
     * Validates whether a given phone number is Bangladeshi or international.
     *
     * @param string $number
     * @return string The original number if valid
     * @throws InvalidArgumentException
     */
    public function validatePhoneNumber(string $number): string
    {
        $regex = '/^(?:\+?88)?0?1[3-9]\d{8}$|^\+?[1-9]\d{9,14}$/';

        if (!preg_match($regex, $number)) {
            throw new InvalidArgumentException('Invalid phone number format.');
        }

        return $number;
    }
}
