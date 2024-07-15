<?php

namespace TRSTDExampleIntegration\COT;

use TRSTDExampleIntegration\COT\COTAuthRepository;
use TRSTD\COT\AuthStorageInterface;
use TRSTD\COT\Token;

class COTAuthDBStorage implements AuthStorageInterface
{
    /**
     * @var COTAuthRepository
     */
    private $cotAuthRepository;

    public function __construct()
    {
        $this->cotAuthRepository = new COTAuthRepository();
    }

    public function set(Token $token, $ctcId)
    {
        $this->cotAuthRepository->saveAuth($token, $ctcId);
    }

    public function getByCtcId($ctcId)
    {
        $cotAuth = $this->cotAuthRepository->getAuthByCtcId($ctcId);

        if ($cotAuth === null) {
            return null;
        }

        return new Token($cotAuth->id_token, $cotAuth->refresh_token, $cotAuth->access_token);
    }

    /**
     * @param string $ctcId
     */
    public function remove($ctcId)
    {
        $this->cotAuthRepository->deleteAuthByCtcId($ctcId);
    }
}
