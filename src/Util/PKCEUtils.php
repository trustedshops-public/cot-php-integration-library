<?php

namespace TRSTD\COT\Util;

final class PKCEUtils
{
    /**
     * @return string
     */
    public static function generateCodeVerifier()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * @param string $codeVerifier code verifier to generate challenge
     * @return string
     */
    public static function generateCodeChallenge($codeVerifier)
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}
