<?php

namespace Backstage\Support;

class DateRange
{
    protected $start_date;
    protected $end_date;
    protected $date_format;
    protected $separator;

    public function __construct(DateTime $start_date, DateTime $end_date, DateFormat $date_format = null, $separator = ' - ')
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->date_format = $date_format ?: new DateFormat;
        $this->separator = $separator;
    }

    public function getRange($with_end_date_year = false)
    {
        $df = $this->date_format;

        if ($this->start_date->isSameDayAs($this->end_date)) {
            return [$this->start_date->format($df->getMonthdayFormat($with_end_date_year))];
        }

        if ($this->start_date->isSameMonthAs($this->end_date)) {
            return $this->formatRange(false, $df->getDayFormat($with_end_date_year));
        }

        if ($this->start_date->isSameYearAs($this->end_date)) {
            return $this->formatRange(false, $with_end_date_year);
        }

        return $this->formatRange();
    }

    public function formatRange($start_format = true, $end_format = true)
    {
        if (is_bool($start_format)) {
            $start_format = $this->date_format->getMonthdayFormat($start_format);
        }
        if (is_bool($end_format)) {
            $end_format = $this->date_format->getMonthdayFormat($end_format);
        }
        return [$this->start_date->format($start_format), $this->end_date->format($end_format)];
    }

    public function getRangeWithSeparator($with_end_date_year = false)
    {
        return implode($this->separator, $this->getRange($with_end_date_year));
    }

    public function __toString()
    {
        return $this->getRangeWithSeparator();
    }

}

