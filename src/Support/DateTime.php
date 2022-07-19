<?php

namespace Backstage\Support;

use DateTimeInterface, DateTimeZone;


class DateTime extends \DateTime implements \JsonSerializable
{
    const DEFAULT_FORMAT = DATE_ISO8601;

    protected $is_dst;
    protected $default_format = self::DEFAULT_FORMAT;

    public function __construct($time='now', $default_format = null, DateTimeZone $timezone = null)
    {
        $this->setDST($time);
        if ($default_format) {
            $this->default_format = $default_format;
        }
        parent::__construct($time, $this->getDefaultTimezone($timezone));
    }

    public static function createFromTimestamp($timestamp, $default_format = null, DateTimeZone $timezone = null)
    {
        $datetime = new static("@$timestamp", $default_format, new DateTimeZone('UTC'));
        $datetime->setTimezone($datetime->getDefaultTimezone($timezone));
        return $datetime;
    }

    public function isBetween(DateTimeInterface $date_before, DateTimeInterface $date_after)
    {
        return $this->isAfter($date_before) && $this->isBefore($date_after);
    }

    public function timestampDiff(DateTimeInterface $date)
    {
        return $this->getTimestamp() - $date->getTimestamp();
    }

    public function isBefore(DateTimeInterface $date)
    {
        return $this->timestampDiff($date) < 0;
    }

    public function isAfter(DateTimeInterface $date)
    {
        return $this->timestampDiff($date) > 0;
    }

    public function isSameDayAs(DateTimeInterface $date)
    {
        return $this->format('Y-m-d') === $date->format('Y-m-d');
    }

    public function isSameMonthAs(DateTimeInterface $date)
    {
        return $this->format('Y-m') === $date->format('Y-m');
    }

    public function isSameYearAs(DateTimeInterface $date)
    {
        return $this->format('Y') === $date->format('Y');
    }

    public function isPast()
    {
        return $this->getTimestamp() < time();
    }

    public function isFuture()
    {
        return $this->getTimestamp() > time();
    }

    public function isDaylightSavings()
    {
        return $this->is_dst;
    }

    public function __toString()
    {
        return $this->format($this->default_format);
    }

    public function jsonSerialize()
    {
        return $this->format(DATE_RFC2822);
    }

    protected function setDST($time)
    {
        $localtime_assoc = localtime(strtotime($time), true);
        $is_dst = !empty($localtime_assoc['is_dst']) || !empty($localtime_assoc['tm_isdst']);
        if (-1 !== $is_dst) $this->is_dst = (bool) $is_dst;
    }

    protected function getDefaultTimezone(DateTimeZone $timezone = null)
    {
        return is_null($timezone) ? new DateTimeZone($this->getDefaultTimezoneName()) : $timezone;
    }

    public function getDefaultTimezoneName()
    {
        if (!function_exists('get_option')) {
            return date_default_timezone_get();
        }
        if ($timezone_string = get_option('timezone_string')) {
            return $timezone_string;
        }
        return timezone_name_from_abbr('', get_option('gmt_offset', 0) * 3600, $this->is_dst);
    }

}