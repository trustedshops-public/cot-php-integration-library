<?php

namespace TRSTD\COT;

final class ConsumerData
{
    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $membershipStatus;

    /**
     * @var string
     */
    private $membershipSince;

    public function __construct(string $firstName, string $membershipStatus, string $membershipSince)
    {
        $this->firstName = $firstName;
        $this->membershipStatus = $membershipStatus;
        $this->membershipSince = $membershipSince;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getMembershipStatus()
    {
        return $this->membershipStatus;
    }

    /**
     * @return string
     */
    public function getMembershipSince()
    {
        return $this->membershipSince;
    }
}
