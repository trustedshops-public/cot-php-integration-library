<?php

// {
//     "statistics": {
//         "averageOrderValue": 1258.95,
//         "maxOrderValue": 3409.9,
//         "orderVolumeLastMonth": 0,
//         "orderVolumeLastYear": 7553.7,
//         "shopsStats": {
//             "X3C0356AC8ACBFC4A364871034758E0DC": {}
//         },
//         "calculatedAt": "2024-07-05T14:28:05Z"
//     },
//     "membershipInfo": {
//         "memberships": [
//             {
//                 "type": "BASIC",
//                 "startDate": "2022-02-11T15:29:54Z"
//             }
//         ]
//     }
// }

namespace COT;

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
