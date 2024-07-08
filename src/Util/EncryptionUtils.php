<?php

namespace COT\Util;

final class EncryptionUtils
{
    /**
     * @param string $key - key used for encryption
     * @param string $value - value to encrypt
     * @return string
     */
    public static function encryptValue($key, $value)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * @param string $key - key used for encryption
     * @param string $value - value to decrypt
     * @return string
     */
    public static function decryptValue($key, $value)
    {
        list($encryptedData, $iv) = explode('::', base64_decode($value), 2);
        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }
}
