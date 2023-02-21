<?php

namespace Letkode\Helpers;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;

final class DateHelper
{
    const FORMAT_SHOW = 'Y-m-d H:i:s';

    private static array $holidays = [];

    public static function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateTime = DateTime::createFromFormat($format, $date);

        return $dateTime && $dateTime->format($format) == $date;
    }

    public static function convertDateToWords($date, string $locale = 'es'): string
    {
        if (null === $date) {
            return '';
        }

        $date = Carbon::create($date)->locale($locale);

        return sprintf('%d de %s de %d', $date->day, $date->monthName, $date->year);
    }

    public static function dateRangeFromDateStart($date, int $quantityInterval = 1, string $typeInterval = 'day'): array
    {
        $date = is_string($date) ? new DateTime($date):$date;

        $range[] = new DateTime($date->format(self::FORMAT_SHOW));
        for ($i = 1; $i < $quantityInterval; $i++) {
            $date->modify(sprintf('+%d %s', 1, $typeInterval));

            $range[] = new DateTime($date->format(self::FORMAT_SHOW));
        }

        return $range;
    }

    public static function agoShort($datetime, bool $time = true): string
    {
        $now = new DateTime('now');

        $datetime = is_object($datetime) ? $datetime:new DateTime($datetime);
        $interval = $datetime->diff($now);

        $arrayHumanize = self::humanizeText($interval, $time);

        return implode('', $arrayHumanize);
    }

    public static function humanizeText(DateInterval $interval, bool $time = true): array
    {
        $years = $interval->y;
        $months = $interval->m;
        $days = $interval->d;
        $hours = $interval->h;
        $minutes = $interval->i;
        $seconds = $interval->s;

        $diffText = [];

        if (abs($years) > 0) {
            $diffText['year'] = abs($years).' año'.(abs($years) == 1 ? '':'s');
            if (abs($months) > 0) {
                $diffText['month'] = ' y '.abs($months).' mes'.(abs($months) == 1 ? '':'es');
            }
        } elseif (abs($months) > 0) {
            $diffText['month'] = abs($months).' mes'.(abs($months) == 1 ? '':'es');
            if (abs($days) > 0) {
                $diffText['day'] = ' y '.abs($days).' día'.(abs($days) == 1 ? '':'s');
            }
        } elseif (abs($days) > 0) {
            $diffText['day'] = abs($days).' día'.(abs($days) == 1 ? '':'s');
            if (abs($hours) > 0 && $time) {
                $diffText['hour'] = ' y '.abs($hours).' hora'.(abs($hours) == 1 ? '':'s');
            }
        } elseif (abs($hours) > 0 && $time) {
            $diffText['hour'] = abs($hours).' hora'.(abs($hours) == 1 ? '':'s');
            if (abs($minutes) > 0) {
                $diffText['minute'] = ' y '.abs($minutes).' minuto'.(abs($minutes) == 1 ? '':'s');
            }
        } elseif (abs($minutes) > 0 && $time) {
            $diffText['minute'] = abs($minutes).' minuto'.(abs($minutes) == 1 ? '':'s');
            if (abs($seconds) > 0) {
                $diffText['second'] = ' y '.abs($minutes).' segundo'.(abs($seconds) == 1 ? '':'s');
            }
        } else {
            if ($time) {
                $diffText['second'] = abs($seconds).' segundo'.(abs($seconds) == 1 ? '':'s');
            }
        }

        return $diffText;
    }

    public static function transformFormatDateFromString(string $date, string $format = 'Y-m-d'): string
    {
        $formatsCheck = ['d/m/Y', 'j/n/Y', 'm/d/Y', 'n/j/Y', 'Y-m-d', 'Y-n-j', 'd-m-Y', 'j-n-Y', 'm-d-Y', 'n-j-Y'];
        foreach ($formatsCheck as $formatCheck) {
            $d = DateTime::createFromFormat($formatCheck, $date);

            if ($d && $d->format($formatCheck) === $date) {
                return $d->format($format);
            }
        }

        return $date;
    }

    public static function blockIntervalDateTime(DateTime $startInit, DateTime $endInit, int $periodInterval = 1): array
    {
        $startTimeDefault = '00:00:00';
        $endTimeDefault = '23:59:59';
        $diff = $startInit->diff($endInit);
        if ($diff->days < $periodInterval) {
            return [
                [
                    'start' => $startInit,
                    'end' => $endInit,
                ],
            ];
        }

        $rangeDateTime = [];
        $start = $startInit;
        $end = $endInit;
        $endIterator = $startInit;

        $a = $end->getTimestamp();
        $b = null;
        while ($a !== $b) {
            $time = $endTimeDefault;
            $addDays = $periodInterval;
            if ($addDays >= $end->diff($endIterator)->days) {
                $time = $end->format('H:i:s');
                $addDays = $end->diff($endIterator)->days;
            }

            $b = $endIterator->getTimestamp();
            if ($a === $b) {
                break;
            }

            $endIterator = (clone $start)->modify(sprintf('this day %s', $time))
                ->modify(sprintf('%d days', $addDays - 1));

            $rangeDateTime[] = [
                'start' => $start,
                'end' => $endIterator,
            ];

            $start = (clone $endIterator)->modify(sprintf('this day %s', $startTimeDefault))
                ->modify('1 days');
        }

        return $rangeDateTime;
    }

    public static function rangeDateByInterval(
        string|DateTime $startDate,
        string|DateTime $endDate,
        array|string|null $dateInterval = null,
        string $formatDate = 'Y-m-d',
        bool $fullDays = true,
        bool $excludeStartDate = false
    ): array {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end->modify('+1 day');

        if (is_null($dateInterval)) {
            return [$start->format($formatDate)];
        }

        $rangeDate = [];
        if (is_array($dateInterval)) {
            foreach ($dateInterval as $dateInt) {
                self::generateRangeDate($rangeDate, $start, $end, $dateInt, $formatDate, $fullDays, $excludeStartDate);
            }
        } else {
            self::generateRangeDate($rangeDate, $start, $end, $dateInterval, $formatDate, $fullDays, $excludeStartDate);
        }

        sort($rangeDate);

        return $rangeDate;
    }

    private static function generateRangeDate(
        &$rangeDate,
        $start,
        $end,
        $dateInterval,
        string $formatDate = 'Y-m-d',
        bool $fullDays = true,
        bool $excludeStartDate = false
    ): void {
        $interval = DateInterval::createFromDateString($dateInterval);
        $period = $excludeStartDate ? new DatePeriod($start, $interval, $end, DatePeriod::EXCLUDE_START_DATE)
            :new DatePeriod($start, $interval, $end);

        foreach ($period as $p) {
            $date = $p->format($formatDate);
            if ($fullDays) {
                $rangeDate[] = $p->format($formatDate);
                continue;
            }
            if (!self::isHolidaysAndWeekend($date)) {
                $rangeDate[] = $p->format($formatDate);
            }
        }
    }

    public static function isHolidaysAndWeekend($date, $lastDay = 'friday'): bool|array
    {
        if (is_array($date)) {
            $resultArr = array();
            foreach ($date as $d) {
                $resultArr[$d] = self::isHolidaysAndWeekend($d, $lastDay);
            }

            return $resultArr;
        } else {
            if (self::$holidays === null) {
                self::$holidays = self::holidays();
            }

            $dt = new DateTime($date);
            $dayNum = date("w", strtotime($lastDay));
            $result = false;

            if (isset(self::$holidays[$date])) {
                $result = true;
            }
            if ($dt->format('w') == 0 || $dt->format('w') > $dayNum) {
                $result = true;
            }
        }

        return $result;
    }

    public static function holidays(?int $year = null): array
    {
        return [];
    }
}