<?php

namespace TRSTD\COT;

use TRSTD\COT\Token;

interface AuthStorageInterface
{
    /**
     * @param string $sub Subject ID
     * @return Token|null
     */
    public function get($sub);

    /**
     * @param string $sub Subject ID
     * @param Token $token Token object
     * @return void
     */
    public function set($sub, Token $token);

    /**
     * @param string $sub Subject ID
     * @return void
     */
    public function remove($sub);
}
