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

    public function get($ctcId)
    {
        $cotAuth = $this->cotAuthRepository->get($ctcId);

        if ($cotAuth === null) {
            return null;
        }

        return new Token($cotAuth->id_token, $cotAuth->refresh_token, $cotAuth->access_token);
    }

    public function set($ctcId, Token $token)
    {
        $this->cotAuthRepository->save($ctcId, $token);
    }

    /**
     * @param string $ctcId
     */
    public function remove($ctcId)
    {
        $this->cotAuthRepository->delete($ctcId);
    }
}
