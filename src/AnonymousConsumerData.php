<?php

namespace TRSTD\COT;

final class AnonymousConsumerData
{
    /**
     * @var General
     */
    private $general;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var PersonWithCommunity
     */
    private $personWithCommunity;

    /**
     * @var PersonWithBrand
     */
    private $personWithBrand;

    public function __construct(General $general, Person $person, PersonWithCommunity $personWithCommunity, PersonWithBrand $personWithBrand)
    {
        $this->general = $general;
        $this->person = $person;
        $this->personWithCommunity = $personWithCommunity;
        $this->personWithBrand = $personWithBrand;
    }

    /**
     * @return General
     */
    public function getGeneral()
    {
        return $this->general;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return PersonWithCommunity
     */
    public function getPersonWithCommunity()
    {
        return $this->personWithCommunity;
    }

    /**
     * @return PersonWithBrand
     */
    public function getPersonWithBrand()
    {
        return $this->personWithBrand;
    }
}

final class General
{
    /**
     * @var string
     */
    private $currency;

    public function __construct(string $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}

final class Person
{
    /**
     * @var Array<Membership>
     */
    private $memberships;

    /**
     * @param Array<Membership> $memberships
     */
    public function __construct(array $memberships)
    {
        $this->memberships = $memberships;
    }

    /**
     * @return Array<Membership>
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
    private $service;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $since;

    public function __construct(string $service, string $type, string $since)
    {
        $this->service = $service;
        $this->type = $type;
        $this->since = $since;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
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
    public function getSince()
    {
        return $this->since;
    }
}

final class PersonWithCommunity
{
    /**
     * @var Lifetime
     */
    private $lifetime;

    /**
     * @var Last365Days
     */
    private $last365days;

    /**
     * @var Last30Days
     */
    private $last30days;

    public function __construct(Lifetime $lifetime, Last365Days $last365days, Last30Days $last30days)
    {
        $this->lifetime = $lifetime;
        $this->last365days = $last365days;
        $this->last30days = $last30days;
    }

    /**
     * @return Lifetime
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @return Last365Days
     */
    public function getLast365days()
    {
        return $this->last365days;
    }

    /**
     * @return Last30Days
     */
    public function getLast30days()
    {
        return $this->last30days;
    }
}

final class Lifetime
{
    /**
     * @var Orders
     */
    private $orders;

    public function __construct(Orders $orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return Orders
     */
    public function getOrders()
    {
        return $this->orders;
    }
}

final class Last365Days
{
    /**
     * @var Orders
     */
    private $orders;

    public function __construct(Orders $orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return Orders
     */
    public function getOrders()
    {
        return $this->orders;
    }
}

final class Last30Days
{
    /**
     * @var Orders
     */
    private $orders;

    public function __construct(Orders $orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return Orders
     */
    public function getOrders()
    {
        return $this->orders;
    }
}

final class PersonWithBrand
{
    /**
     * @var boolean
     */
    private $recurringCustomer;

    /**
     * @var LastOrderTimeFrame
     */
    private $lastOrderTimeFrame;

    /**
     * @var Lifetime
     */
    private $lifetime;

    /**
     * @param boolean $recurringCustomer
     * @param LastOrderTimeFrame $lastOrderTimeFrame
     * @param Lifetime $lifetime
     */
    public function __construct($recurringCustomer, LastOrderTimeFrame $lastOrderTimeFrame, Lifetime $lifetime)
    {
        $this->recurringCustomer = $recurringCustomer;
        $this->lastOrderTimeFrame = $lastOrderTimeFrame;
        $this->lifetime = $lifetime;
    }

    /**
     * @return boolean
     */
    public function getRecurringCustomer()
    {
        return $this->recurringCustomer;
    }

    /**
     * @return LastOrderTimeFrame
     */
    public function getLastOrderTimeFrame()
    {
        return $this->lastOrderTimeFrame;
    }

    /**
     * @return Lifetime
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }
}

final class Orders
{
    /**
     * @var float
     */
    private $volumeAvg;

    /**
     * @var float
     */
    private $volumeMax;

    /**
     * @var integer
     */
    private $count;

    /**
     * @param float $volumeAvg
     * @param float $volumeMax
     * @param integer $count
     */
    public function __construct($volumeAvg, $volumeMax, $count)
    {
        $this->volumeAvg = $volumeAvg;
        $this->volumeMax = $volumeMax;
        $this->count = $count;
    }

    /**
     * @return float
     */
    public function getVolumeAvg()
    {
        return $this->volumeAvg;
    }

    /**
     * @return float
     */
    public function getVolumeMax()
    {
        return $this->volumeMax;
    }

    /**
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }
}

final class OrdersFrequency
{
    /**
     * @var integer
     */
    private $value;

    /**
     * @var FrequencyTimeFrame
     */
    private $timeFrame;

    /**
     * @param integer $value
     * @param FrequencyTimeFrame $timeFrame
     */
    public function __construct($value, FrequencyTimeFrame $timeFrame)
    {
        $this->value = $value;
        $this->timeFrame = $timeFrame;
    }

    /**
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return FrequencyTimeFrame
     */
    public function getTimeFrame()
    {
        return $this->timeFrame;
    }
}

final class OrdersVolume
{
    /**
     * @var float
     */
    private $volumeAvg;

    /**
     * @var float
     */
    private $volumeMax;

    /**
     * @param float $volumeAvg
     * @param float $volumeMax
     */
    public function __construct($volumeAvg, $volumeMax)
    {
        $this->volumeAvg = $volumeAvg;
        $this->volumeMax = $volumeMax;
    }

    /**
     * @return float
     */
    public function getVolumeAvg()
    {
        return $this->volumeAvg;
    }

    /**
     * @return float
     */
    public function getVolumeMax()
    {
        return $this->volumeMax;
    }
}

final class FrequencyTimeFrame
{
    public const ONE_DAY = 'ONE_DAY';
    public const TWO_DAYS = 'TWO_DAYS';
    public const THREE_DAYS = 'THREE_DAYS';
    public const ONE_WEEK = 'ONE_WEEK';
    public const TWO_WEEKS = 'TWO_WEEKS';
    public const ONE_MONTH = 'ONE_MONTH';
    public const THREE_MONTHS = 'THREE_MONTHS';
    public const SIX_MONTHS = 'SIX_MONTHS';
    public const ONE_YEAR = 'ONE_YEAR';
    public const TWO_YEARS = 'TWO_YEARS';
    public const FIVE_YEARS = 'FIVE_YEARS';
    public const MORE_THAN_FIVE_YEARS = 'MORE_THAN_FIVE_YEARS';
}

final class LastOrderTimeFrame
{
    public const YESTERDAY = 'YESTERDAY';
    public const LAST_TWO_DAYS = 'LAST_TWO_DAYS';
    public const LAST_THREE_DAYS = 'LAST_THREE_DAYS';
    public const LAST_WEEK = 'LAST_WEEK';
    public const LAST_TWO_WEEKS = 'LAST_TWO_WEEKS';
    public const LAST_MONTH = 'LAST_MONTH';
    public const LAST_TWO_MONTHS = 'LAST_TWO_MONTHS';
    public const LAST_THREE_MONTHS = 'LAST_THREE_MONTHS';
    public const LAST_SIX_MONTHS = 'LAST_SIX_MONTHS';
    public const LAST_YEAR = 'LAST_YEAR';
    public const LAST_TWO_YEARS = 'LAST_TWO_YEARS';
    public const LAST_FIVE_YEARS = 'LAST_FIVE_YEARS';
    public const MORE_THAN_FIVE_YEARS = 'MORE_THAN_FIVE_YEARS';
}
