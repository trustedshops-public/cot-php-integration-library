<?php

namespace TRSTD\COT;

final class AnonymousConsumerData
{
    /**
     * @var Statistics
     */
    private $statistics;

    /**
     * @var MembershipInfo
     */
    private $membershipInfo;

    /**
     * @param Statistics $statistics
     * @param MembershipInfo $membershipInfo
     */
    public function __construct($statistics, $membershipInfo)
    {
        $this->statistics = $statistics;
        $this->membershipInfo = $membershipInfo;
    }

    /**
     * @return Statistics
     */
    public function getStatistics()
    {
        return $this->statistics;
    }

    /**
     * @return MembershipInfo
     */
    public function getMembershipInfo()
    {
        return $this->membershipInfo;
    }
}

final class Statistics
{
    /**
     * @var float
     */
    private $averageOrderValue;

    /**
     * @var float
     */
    private $maxOrderValue;

    /**
     * @var float
     */
    private $orderVolumeLastMonth;

    /**
     * @var float
     */
    private $orderVolumeLastYear;

    /**
     * @var array<string, object>
     */
    private $shopsStats;

    /**
     * @var string
     */
    private $calculatedAt;

    /**
     * @param float $averageOrderValue
     * @param float $maxOrderValue
     * @param float $orderVolumeLastMonth
     * @param float $orderVolumeLastYear
     * @param array<string, object> $shopsStats
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

    /**
     * @return float
     */
    public function getAverageOrderValue()
    {
        return $this->averageOrderValue;
    }

    /**
     * @return float
     */
    public function getMaxOrderValue()
    {
        return $this->maxOrderValue;
    }

    /**
     * @return float
     */
    public function getOrderVolumeLastMonth()
    {
        return $this->orderVolumeLastMonth;
    }

    /**
     * @return float
     */
    public function getOrderVolumeLastYear()
    {
        return $this->orderVolumeLastYear;
    }

    /**
     * @return array<string, object>
     */
    public function getShopsStats()
    {
        return $this->shopsStats;
    }

    /**
     * @return string
     */
    public function getCalculatedAt()
    {
        return $this->calculatedAt;
    }
}

final class MembershipInfo
{
    /**
     * @var array<Membership>
     */
    private $memberships;

    /**
     * @param array<Membership> $memberships
     */
    public function __construct($memberships)
    {
        $this->memberships = $memberships;
    }

    /**
     * @return array<Membership> $memberships
     */
    public function getMemberships()
    {
        return $this->memberships;
    }
}

final class Membership
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $startDate;

    /**
     * @param string $type
     * @param string $startDate
     */
    public function __construct($type, $startDate)
    {
        $this->type = $type;
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
}
