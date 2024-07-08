<?php

namespace COT\Util;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Math\BigInteger;

final class JWTKUtils
{
    /**
     * @param string $token - token to decode
     * @param bool $validateExp - if true, validates expiration
     * @return object
     */
    public static function decodeToken($jwk, $token, $validateExp = true)
    {
        $pem = JWTKUtils::jwkToPem($jwk);

        //TODO: find a way to validate token without validating exp
        if (!$validateExp) {
            $tks = explode('.', $token);
            return JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
        }

        return JWT::decode($token, new Key($pem, $jwk->alg));
    }

    /**
     * @param object $jwk
     * @return string
     */
    public static function jwkToPem($jwk)
    {
        $n = new BigInteger(base64_decode(strtr($jwk->n, '-_', '+/')), 256);
        $e = new BigInteger(base64_decode(strtr($jwk->e, '-_', '+/')), 256);

        $publicKey = PublicKeyLoader::load(['n' => $n, 'e' => $e]);

        return $publicKey->toString('PKCS8');
    }
}
