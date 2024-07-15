<?php

namespace TRSTD\COT;

use TRSTD\COT\Token;

interface AuthStorageInterface
{
    /**
     * @param string $ctcId CTC ID
     * @return Token|null
     */
    public function get($ctcId);

    /**
     * @param string $ctcId CTC ID
     * @param Token $token Token object
     */
    public function set($ctcId, Token $token);

    /**
     * @param string $ctcId CTC ID
     */
    public function remove($ctcId);
}
