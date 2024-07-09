<?php

namespace TRSTD\COT;

class AnonymousConsumerData
{
    /**
     * @var Statistics
     */
    public $statistics;

    /**
     * @var MembershipInfo
     */
    public $membershipInfo;

    /**
     * @param Statistics $statistics
     * @param MembershipInfo $membershipInfo
     */
    public function __construct($statistics, $membershipInfo)
    {
        $this->statistics = $statistics;
        $this->membershipInfo = $membershipInfo;
    }
}

class Statistics
{
    /**
     * @var float
     */
    public $averageOrderValue;

    /**
     * @var float
     */
    public $maxOrderValue;

    /**
     * @var float
     */
    public $orderVolumeLastMonth;

    /**
     * @var float
     */
    public $orderVolumeLastYear;

    /**
     * @var array
     */
    public $shopsStats;

    /**
     * @var string
     */
    public $calculatedAt;

    /**
     * @param float $averageOrderValue
     * @param float $maxOrderValue
     * @param float $orderVolumeLastMonth
     * @param float $orderVolumeLastYear
     * @param array $shopsStats
     * @param string $calculatedAt
     */
    public function __construct($averageOrderValue, $maxOrderValue, $orderVolumeLastMonth, $orderVolumeLastYear, $shopsStats, $calculatedAt)
    {
        $this->averageOrderValue = $averageOrderValue;
        $this->maxOrderValue = $maxOrderValue;
        $this->orderVolumeLastMonth = $orderVolumeLastMonth;
        $this->orderVolumeLastYear = $orderVolumeLastYear;
        $this->shopsStats = $shopsStats;
        $this->calculatedAt = $calculatedAt;
    }
}

class MembershipInfo
{
    /**
     * @var Membership[]
     */
    public $memberships;

    /**
     * @param Membership[] $memberships
     */
    public function __construct($memberships)
    {
        $this->memberships = $memberships;
    }
}

class Membership
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $startDate;

    /**
     * @param string $type
     * @param string $startDate
     */
    public function __construct($type, $startDate)
    {
        $this->type = $type;
        $this->startDate = $startDate;
    }
}
