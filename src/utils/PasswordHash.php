<?php

namespace Src\utils;

class PasswordHash
{

    /**
     * Hash input password and return hashed one
     * @param string $password
     * @return string
     */
    public static function hash(string $password): string
    {
        $options = [
            'cost' => 12
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    /**
     * Verify password to hashed password
     * @param string $password
     * @param string $hashed
     * @return bool
     */
    public static function verify(string $password, string $hashed): bool
    {
        return password_verify($password, $hashed);
    }

}