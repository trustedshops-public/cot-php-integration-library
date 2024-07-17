<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TRSTD\COT\Util\EncryptionUtils;

final class EncryptionUtilsTest extends TestCase
{
    /**
     * @var string
     */
    private $key = 'secretKey1234567890123456'; // 256-bit key for AES-256

    /**
     * @covers TRSTD\COT\Util\EncryptionUtils::encryptValue
     * @covers TRSTD\COT\Util\EncryptionUtils::decryptValue
     * @return void
     */
    public function testEncryptionAndDecryption()
    {
        $originalValue = 'Hello, World!';
        $encryptedValue = EncryptionUtils::encryptValue($this->key, $originalValue);
        $decryptedValue = EncryptionUtils::decryptValue($this->key, $encryptedValue);

        $this->assertSame($originalValue, $decryptedValue);
    }

    /**
     * @covers TRSTD\COT\Util\EncryptionUtils::encryptValue
     * @return void
     */
    public function testEncryptionUniqueness()
    {
        $originalValue = 'Hello, World!';
        $encryptedValue1 = EncryptionUtils::encryptValue($this->key, $originalValue);
        $encryptedValue2 = EncryptionUtils::encryptValue($this->key, $originalValue);

        $this->assertNotSame($encryptedValue1, $encryptedValue2);
    }

    /**
     * @covers TRSTD\COT\Util\EncryptionUtils::decryptValue
     * @return void
     */
    public function testDecryptionWithWrongKey()
    {
        $originalValue = 'Hello, World!';
        $wrongKey = 'wrongKey1234567890123456';
        $encryptedValue = EncryptionUtils::encryptValue($this->key, $originalValue);
        $decryptedValue = EncryptionUtils::decryptValue($wrongKey, $encryptedValue);

        $this->assertFalse($decryptedValue);
    }

    /**
     * @covers TRSTD\COT\Util\EncryptionUtils::decryptValue
     * @return void
     */
    public function testDecryptionWithAlteredEncryptedData()
    {
        $originalValue = 'Hello, World!';
        $encryptedValue = EncryptionUtils::encryptValue($this->key, $originalValue);
        // Alter the encrypted data
        $alteredEncryptedValue = substr_replace($encryptedValue, 'a', -2, 1);
        $decryptedValue = EncryptionUtils::decryptValue($this->key, $alteredEncryptedValue);

        $this->assertNotSame($originalValue, $decryptedValue);
    }
}
