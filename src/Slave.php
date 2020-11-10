<?php

namespace SlaveMarket;

/**
 * Раб (Бедняга :-()
 *
 * @package SlaveMarket
 */
class Slave
{
    public const MAX_WORK_HOURS_IN_DAY = 16;

    private const SLAVE_LEASE_HOURS_ERROR_TEMPLATE = 'Ошибка. Раб #%s "%s" занят. Занятые часы: %s';
    private const SLAVE_LEASE_DAYS_OVER_LIMIT_ERROR_TEMPLATE = 'Ошибка. Раб #%s "%s" не может работать больше %s часов в день. Перегруженные дни: %s';

    /** @var int id раба */
    protected $id;

    /** @var string имя раба */
    protected $name;

    /** @var float Стоимость раба за час работы */
    protected $pricePerHour;

    /**
     * Slave constructor.
     *
     * @param int    $id
     * @param string $name
     * @param float  $pricePerHour
     */
    public function __construct(int $id, string $name, float $pricePerHour)
    {
        $this->id = $id;
        $this->name = $name;
        $this->pricePerHour = $pricePerHour;
    }

    /**
     * Возвращает id раба
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Возвращает имя раба
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Возвращает стоимость раба за час
     *
     * @return float
     */
    public function getPricePerHour(): float
    {
        return $this->pricePerHour;
    }

    /**
     * @param int $hours
     *
     * @return float
     */
    public function calcLeasePrice(int $hours): float
    {
        return (float)bcmul($this->pricePerHour, $hours, 2);
    }

    /**
     * @param array $errorHours
     *
     * @return string
     */
    public function getLeaseHoursError(array $errorHours): string
    {
        $stringHours = implode('", "', $errorHours);
        $stringHours = '"' . $stringHours . '"';

        return sprintf(self::SLAVE_LEASE_HOURS_ERROR_TEMPLATE, $this->getId(), $this->getName(), $stringHours);
    }

    /**
     * @param array $errorDays
     *
     * @return string
     */
    public function getLeaseOverLimitDaysError(array $errorDays): string
    {
        $stringDays = implode('", "', $errorDays);
        $stringDays = '"' . $stringDays . '"';

        return sprintf(self::SLAVE_LEASE_DAYS_OVER_LIMIT_ERROR_TEMPLATE, $this->getId(), $this->getName(), self::MAX_WORK_HOURS_IN_DAY, $stringDays);
    }
}
