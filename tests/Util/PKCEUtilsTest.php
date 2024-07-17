<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TRSTD\COT\Util\PKCEUtils;

final class PKCEUtilsTest extends TestCase
{
    /**
     * @covers TRSTD\COT\Util\PKCEUtils::generateCodeVerifier
     * @return void
     */
    public function testGenerateCodeVerifier()
    {
        $codeVerifier = PKCEUtils::generateCodeVerifier();

        $this->assertIsString($codeVerifier);
        $this->assertGreaterThanOrEqual(43, strlen($codeVerifier)); // Minimum length for base64-encoded 32-byte string
        $this->assertLessThanOrEqual(128, strlen($codeVerifier)); // Maximum length allowed for code verifier
    }

    /**
     * @covers TRSTD\COT\Util\PKCEUtils::generateCodeChallenge
     * @return void
     */
    public function testGenerateCodeChallenge()
    {
        $codeVerifier = PKCEUtils::generateCodeVerifier();
        $codeChallenge = PKCEUtils::generateCodeChallenge($codeVerifier);

        $this->assertIsString($codeChallenge);
        // Length of base64-encoded SHA-256 hash
        $this->assertEquals(43, strlen($codeChallenge));
    }

    /**
     * @covers TRSTD\COT\Util\PKCEUtils::generateCodeChallenge
     * @return void
     */
    public function testCodeChallengeUniqueness()
    {
        $codeVerifier1 = PKCEUtils::generateCodeVerifier();
        $codeChallenge1 = PKCEUtils::generateCodeChallenge($codeVerifier1);

        $codeVerifier2 = PKCEUtils::generateCodeVerifier();
        $codeChallenge2 = PKCEUtils::generateCodeChallenge($codeVerifier2);

        $this->assertNotEquals($codeChallenge1, $codeChallenge2);
    }

    /**
     * @covers TRSTD\COT\Util\PKCEUtils::generateCodeChallenge
     * @return void
     */
    public function testCodeChallengeConsistency()
    {
        $codeVerifier = PKCEUtils::generateCodeVerifier();
        $codeChallenge1 = PKCEUtils::generateCodeChallenge($codeVerifier);
        $codeChallenge2 = PKCEUtils::generateCodeChallenge($codeVerifier);

        $this->assertEquals($codeChallenge1, $codeChallenge2);
    }
}
