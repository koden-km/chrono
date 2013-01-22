<?php
namespace Icecave\Chrono;

use Icecave\Chrono\Format\DefaultFormatter;
use Icecave\Chrono\Format\FormatterInterface;
use Icecave\Chrono\Support\Normalizer;
use Icecave\Chrono\TypeCheck\TypeCheck;

/**
 * Represents a date.
 */
class Date implements TimePointInterface
{
    /**
     * @param integer       $year     The year component of the date.
     * @param integer       $month    The month component of the date.
     * @param integer       $day      The day component of the date.
     * @param TimeZone|null $timeZone The time zone of the time, or null to use UTC.
     */
    public function __construct(
        $year,
        $month,
        $day,
        TimeZone $timeZone = null
    ) {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        Normalizer::normalizeDate($year, $month, $day);

        if ($timeZone === null) {
            $timeZone = new TimeZone;
        }

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->timeZone = $timeZone;
    }

    /**
     * @return integer The year component of the date.
     */
    public function year()
    {
        $this->typeCheck->year(func_get_args());

        return $this->year;
    }

    /**
     * @return integer The month component of the date.
     */
    public function month()
    {
        $this->typeCheck->month(func_get_args());

        return $this->month;
    }

    /**
     * @return integer The day component of the date.
     */
    public function day()
    {
        $this->typeCheck->day(func_get_args());

        return $this->day;
    }

    /**
     * Convert this time to a different timezone.
     *
     * Note that this method returns a {@see DateTime} instance, and not a {@see Date}.
     *
     * @param TimeZone $timeZone The target timezone
     *
     * @return DateTime
     */
    public function toTimeZone(TimeZone $timeZone)
    {
        $this->typeCheck->toTimeZone(func_get_args());

        if ($this->timeZone()->compare($timeZone) === 0) {
            return $this;
        }

        $offset = $timeZone->offset()
                - $this->timeZone()->offset();

        return new DateTime(
            $this->year(),
            $this->month(),
            $this->day(),
            0,
            0,
            $offset,
            $timeZone
        );
    }

    /**
     * Convert this time to the UTC timezone.
     *
     * Note that this method returns a {@see DateTime} instance, and not a {@see Date}.
     *
     * @return DateTime
     */
    public function toUtc()
    {
        $this->typeCheck->toUtc(func_get_args());

        return $this->toTimeZone(new TimeZone);
    }

    /**
     * @return TimeZone The time zone of the time.
     */
    public function timeZone()
    {
        $this->typeCheck->timeZone(func_get_args());

        return $this->timeZone;
    }

    /**
     * Create a {@see DateTime} instance from this date and the given {@see Time} component.
     *
     * The timezone of the time component will be coverted to the timezone of this date.
     *
     * @param Time $time The time component.
     *
     * @return DateTime A {@see DateTime instance} consiting of this date and the given time component.
     */
    public function at(Time $time)
    {
        $time = $time->toTimeZone($this->timeZone());

        return new DateTime(
            $this->year(),
            $this->month(),
            $this->day(),
            $time->hours(),
            $time->minutes(),
            $time->seconds(),
            $this->timeZone()
        );
    }

    /**
     * Perform a {@see strcmp} style comparison with another time point.
     *
     * @param TimePointInterface $timePoint The time point to compare.
     *
     * @return integer 0 if $this and $timePoint are equal, <0 if $this < $timePoint, or >0 if $this > $timePoint.
     */
    public function compare(TimePointInterface $timePoint)
    {
        $this->typeCheck->compare(func_get_args());

        // Identical ...
        if ($this === $timePoint) {
            return 0;

        // Another date ...
        } elseif ($timePoint instanceof self) {
            return $this->year() - $timePoint->year()
                ?: $this->month() - $timePoint->month()
                ?: $this->day() - $timePoint->day()
                ?: $this->timeZone()->compare($timePoint->timeZone());
        }

        // Fallback to timestamp calculation ...
        return $this->unixTime() - $timePoint->unixTime();
    }

    /**
     * @return integer The number of seconds since unix epoch (1970-01-01 00:00:00+00:00).
     */
    public function unixTime()
    {
        $this->typeCheck->unixTime(func_get_args());

        return gmmktime(
            0,
            0,
            0,
            $this->month(),
            $this->day(),
            $this->year()
        ) - $this->timeZone()->offset();
    }

    /**
     * @param string                  $formatSpecifier The format of the output string.
     * @param FormatterInterface|null $formatter       The formatter to use, or null to use the default.
     *
     * @return string The formatted string.
     */
    public function format($formatSpecifier, FormatterInterface $formatter = null)
    {
        if (null === $formatter) {
            $formatter = DefaultFormatter::instance();
        }

        return $formatter->formatDate($this, $formatSpecifier);
    }

    /**
     * @return string A string representing this object in an ISO compatible format (YYYY-MM-DD).
     */
    public function isoString()
    {
        $this->typeCheck->isoString(func_get_args());

        return sprintf(
            '%04d-%02d-%02d',
            $this->year(),
            $this->month(),
            $this->day()
        );
    }

    /**
     * @return string A string representing this object in an ISO compatible format (YYYY-MM-DD).
     */
    public function __toString()
    {
        return $this->isoString();
    }

    private $typeCheck;
    private $year;
    private $month;
    private $day;
    private $timeZone;
}
