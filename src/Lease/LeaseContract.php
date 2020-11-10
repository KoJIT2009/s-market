<?php

namespace SlaveMarket\Lease;

use SlaveMarket\Master;
use SlaveMarket\Slave;

/**
 * Договор аренды
 *
 * @package SlaveMarket\Lease
 */
class LeaseContract
{
    /** @var Master Хозяин */
    private $master;

    /** @var Slave Раб */
    private $slave;

    /** @var float Стоимость */
    private $price = 0;

    /** @var LeaseHour[] Список арендованных часов */
    private $leasedHours = [];

    /** @var LeaseHour[] */
    private $leasedHoursKeyHour = [];

    public function __construct(Master $master, Slave $slave, float $price, array $leasedHours)
    {
        $this->master = $master;
        $this->slave = $slave;
        $this->price = $price;
        $this->leasedHours = $leasedHours;

        $this->calcLeasedHoursKeyHour();
    }

    private function calcLeasedHoursKeyHour(): void
    {
        foreach ($this->leasedHours as $hour) {
            $this->leasedHoursKeyHour[$hour->getDateString()] = $hour;
        }
    }

    /**
     * @return Master
     */
    public function getMaster(): Master
    {
        return $this->master;
    }

    /**
     * @return Slave
     */
    public function getSlave(): Slave
    {
        return $this->slave;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return LeaseHour[]
     */
    public function getLeasedHours(): array
    {
        return $this->leasedHours;
    }

    /**
     * @return array
     */
    public function getLeasedHoursKeyHour(): array
    {
        return $this->leasedHoursKeyHour;
    }
}
