<?php

namespace COT;

/**
 * Class Token
 * @package COT
 */
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
     * @param string $idToken
     * @param string $refreshToken
     * @param string $accessToken
     */
    public function __construct($idToken, $refreshToken, $accessToken)
    {
        $this->idToken = $idToken;
        $this->refreshToken = $refreshToken;
        $this->accessToken = $accessToken;
    }
}
