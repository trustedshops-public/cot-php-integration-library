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

    public function get($sub)
    {
        $cotAuth = $this->cotAuthRepository->get($sub);

        if ($cotAuth === null) {
            return null;
        }

        return new Token($cotAuth->id_token, $cotAuth->refresh_token, $cotAuth->access_token);
    }

    public function set($sub, Token $token)
    {
        $this->cotAuthRepository->save($sub, $token);
    }

    /**
     * @param string $sub
     */
    public function remove($sub)
    {
        $this->cotAuthRepository->delete($sub);
    }
}
