<?php

namespace TRSTD\COT;

use TRSTD\COT\Token;

interface AuthStorageInterface
{
    /**
     * @param Token $token Token object
     * @param string $ctcId CTC ID
     */
    public function set(Token $token, $ctcId);

    /**
     * @param string $ctcId CTC ID
     * @return Token|null
     */
    public function getByCtcId($ctcId);

    /**
     * @param string $ctcId CTC ID
     */
    public function remove($ctcId);
}
