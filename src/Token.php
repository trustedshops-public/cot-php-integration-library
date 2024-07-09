<?php

namespace TRSTD\COT;

class Token
{
    /**
     * @var string
     */
    public $idToken;

    /**
     * @var string
     */
    public $refreshToken;

    /**
     * @var string
     */
    public $accessToken;

    /**
     * @param string $idToken The ID token as base64 encoded JSON
     * @param string $refreshToken The refresh token used to get a new access token
     * @param string $accessToken The access token used to authenticate requests
     */
    public function __construct($idToken, $refreshToken, $accessToken)
    {
        $this->idToken = $idToken;
        $this->refreshToken = $refreshToken;
        $this->accessToken = $accessToken;
    }
}
