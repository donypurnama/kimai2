<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use App\Entity\User;
use App\Model\Statistic\StatisticDate;
use DateTime;
use DateTimeInterface;

final class DailyStatistic
{
    /**
     * @var array<string, StatisticDate>
     */
    private $days = [];
    private $begin;
    private $end;
    private $user;

    public function __construct(DateTime $begin, DateTime $end, User $user)
    {
        $this->begin = clone $begin;
        $this->end = clone $end;
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    private function setupDays(): void
    {
        if (!empty($this->days)) {
            return;
        }

        $tmp = clone $this->begin;
        $tmp->setTime(0, 0, 0);
        while ($tmp < $this->end) {
            $id = $tmp->format('Y-m-d');
            $this->days[$id] = new StatisticDate(clone $tmp);
            $tmp->modify('+1 day');
        }
    }

    /**
     * @return StatisticDate[]
     */
    public function getDays(): array
    {
        $this->setupDays();

        return array_values($this->days);
    }

    public function getDayByDateTime(\DateTimeInterface $date): ?StatisticDate
    {
        return $this->getDay($date->format('Y'), $date->format('m'), $date->format('d'));
    }

    public function getDayByReportDate(string $date): ?StatisticDate
    {
        $this->setupDays();

        if (!isset($this->days[$date])) {
            return null;
        }

        return $this->days[$date];
    }

    public function getDay(string $year, string $month, string $day): ?StatisticDate
    {
        if ((int) $month < 10) {
            $month = '0' . (int) $month;
        }

        if ((int) $day < 10) {
            $day = '0' . (int) $day;
        }

        $date = $year . '-' . $month . '-' . $day;

        return $this->getDayByReportDate($date);
    }

    /**
     * @return DateTimeInterface[]
     */
    public function getDateTimes(): array
    {
        $this->setupDays();
        $all = [];

        foreach ($this->days as $id => $day) {
            $all[] = $day->getDate();
        }

        return $all;
    }
}
