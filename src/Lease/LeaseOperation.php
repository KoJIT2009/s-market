<?php

namespace SlaveMarket\Lease;

use DateInterval;
use DateTime;
use ErrorException;
use Exception;
use SlaveMarket\Master;
use SlaveMarket\MastersRepository;
use SlaveMarket\Slave;
use SlaveMarket\SlavesRepository;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperation
{
    /**
     * @var LeaseContractsRepository
     */
    protected $contractsRepository;

    /**
     * @var MastersRepository
     */
    protected $mastersRepository;

    /**
     * @var SlavesRepository
     */
    protected $slavesRepository;

    /**
     * LeaseOperation constructor.
     *
     * @param LeaseContractsRepository $contractsRepo
     * @param MastersRepository        $mastersRepo
     * @param SlavesRepository         $slavesRepo
     */
    public function __construct(LeaseContractsRepository $contractsRepo, MastersRepository $mastersRepo, SlavesRepository $slavesRepo)
    {
        $this->contractsRepository = $contractsRepo;
        $this->mastersRepository = $mastersRepo;
        $this->slavesRepository = $slavesRepo;
    }

    /**
     * Выполнить операцию
     *
     * @param LeaseRequest $request
     *
     * @return LeaseResponse
     * @throws ErrorException
     */
    public function run(LeaseRequest $request): LeaseResponse
    {
        $response = new LeaseResponse();

        try {
            $master = $this->mastersRepository->getById($request->masterId);

            if (null === $master) {
                throw new ErrorException('Мастер не найден');
            }

            $slave = $this->slavesRepository->getById($request->slaveId);

            if (null === $slave) {
                throw new ErrorException('Раб не найден');
            }

            $fromDateTime = $request->getTimeFromDateTime();
            $toDateTime = $request->getTimeToDateTime();

            $contracts = $this->contractsRepository->getForSlave(
                $slave->getId(),
                $fromDateTime->format('Y-m-d'),
                $toDateTime->format('Y-m-d')
            );

            [$newLeaseHours, $newLeaseHoursInDays] = $this->getNewLeasedHours($fromDateTime, $toDateTime);

            $hoursError = $this->validateLeaseHoursError($master, $contracts, $newLeaseHours);

            if (count($hoursError) > 0) {
                throw new ErrorException($slave->getLeaseHoursError($hoursError));
            }

            $overLimitDays = $this->validateDayOverLimitHours($newLeaseHoursInDays);

            if (count($overLimitDays) > 0) {
                throw new ErrorException($slave->getLeaseOverLimitDaysError($overLimitDays));
            }

            $payedHours = $this->calcPayedHours($newLeaseHoursInDays);
            $leaseContract = new LeaseContract($master, $slave, $slave->calcLeasePrice($payedHours), $newLeaseHours);

            $response->setLeaseContract($leaseContract);
        } catch (ErrorException $exception) {
            $response->addError($exception->getMessage());
        } catch (Exception $exception) {
            $response->addError('Ошибка');

            //throw $exception;
        }

        return $response;
    }

    /**
     * @param DateTime $fromDateTime
     * @param DateTime $toDateTime
     *
     * @return array
     */
    private function getNewLeasedHours(DateTime $fromDateTime, DateTime $toDateTime): array
    {
        $clonedFromDateTime = clone $fromDateTime;

        $leaseHoursAll = [];
        $leaseHoursInDays = [];


        while ($clonedFromDateTime->getTimestamp() <= $toDateTime->getTimestamp()) {
            $dateTimeHour = $clonedFromDateTime->format('Y-m-d H');
            $dateTimeDay = $clonedFromDateTime->format('Y-m-d');

            $leaseHoursAll[$dateTimeHour] = new LeaseHour($dateTimeHour);
            $leaseHoursInDays[$dateTimeDay][] = $dateTimeHour;


            $clonedFromDateTime->add(new DateInterval('PT1H'));
        }

        return [$leaseHoursAll, $leaseHoursInDays];
    }

    /**
     * @param Master          $master
     * @param LeaseContract[] $alreadyLeaseContracts
     * @param LeaseHour[]     $newLeaseHours
     *
     * @return array
     */
    private function validateLeaseHoursError(Master $master, array $alreadyLeaseContracts, array $newLeaseHours): array
    {
        $leaseHoursError = [];

        foreach ($alreadyLeaseContracts as $alreadyLeaseContract) {
            if ($master->isVIP() && !$alreadyLeaseContract->getMaster()->isVIP()) {
                break;
            }

            /**
             * @var string    $time
             * @var LeaseHour $intersect
             */
            foreach (array_intersect_key($newLeaseHours, $alreadyLeaseContract->getLeasedHoursKeyHour()) as $time => $intersect) {
                $leaseHoursError[] = $time;
            }
        }

        return $leaseHoursError;
    }

    /**
     * @param array $leaseHoursInDay
     *
     * @return array
     */
    private function validateDayOverLimitHours(array $leaseHoursInDay): array
    {
        $overLimitDays = [];

        foreach ($leaseHoursInDay as $day => $hoursInDay) {
            $countHoursInDay = count($hoursInDay);

            if ($countHoursInDay >= Slave::MAX_WORK_HOURS_IN_DAY && $countHoursInDay < 24) {
                $overLimitDays[] = $day;
            }
        }

        return $overLimitDays;
    }

    /**
     * @param array $leaseHoursInDay
     *
     * @return int
     */
    private function calcPayedHours(array $leaseHoursInDay): int
    {
        $payedHours = 0;

        foreach ($leaseHoursInDay as $hoursInDay) {
            $countHoursInDay = count($hoursInDay);

            if (24 === $countHoursInDay) {
                $payedHours += Slave::MAX_WORK_HOURS_IN_DAY;
            } else {
                $payedHours += $countHoursInDay;
            }
        }

        return $payedHours;
    }
}
