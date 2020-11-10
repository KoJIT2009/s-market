<?php

namespace SlaveMarket\Lease;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Запрос на аренду раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseRequest
{
    /** @var int id хозяина */
    public $masterId;

    /** @var int id раба */
    public $slaveId;

    /** @var string время начала работ Y-m-d H:i:s */
    public $timeFrom;

    /** @var string время окончания работ Y-m-d H:i:s */
    public $timeTo;

    /**
     * @return DateTime
     * @throws Exception
     */
    public function getTimeFromDateTime(): DateTime
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->timeFrom, new DateTimeZone('GMT'));

        return $this->getHouredDateTime($dateTime);
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    public function getTimeToDateTime(): DateTime
    {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->timeTo, new DateTimeZone('GMT'));

        return $this->getHouredDateTime($dateTime);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return DateTime
     */
    private function getHouredDateTime(DateTime $dateTime): DateTime
    {
        $timeString = $dateTime->format('Y-m-d H');

        return DateTime::createFromFormat('Y-m-d H', $timeString, new DateTimeZone('GMT'));
    }
}
