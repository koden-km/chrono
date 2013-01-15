<?php
namespace Icecave\Chrono\Clock;

use Icecave\Chrono\Date;
use Icecave\Chrono\DateTime;
use Icecave\Chrono\Month;
use Icecave\Chrono\Time;
use Icecave\Chrono\TimeZone;
use Icecave\Chrono\Year;

interface ClockInterface
{
    /**
     * @return Time The current local time.
     */
    public function localTime();

    /**
     * @return DateTime The current local date/time.
     */
    public function localDateTime();

    /**
     * @return Date The current local date.
     */
    public function localDate();

    /**
     * @return Month The current local month.
     */
    public function localMonth();

    /**
     * @return Year The current local year.
     */
    public function localYear();

    /**
     * @return Time The current UTC time.
     */
    public function utcTime();

    /**
     * @return DateTime The current UTC date/time.
     */
    public function utcDateTime();

    /**
     * @return Date The current UTC date.
     */
    public function utcDate();

    /**
     * @return Month The current UTC month.
     */
    public function utcMonth();

    /**
     * @return Year The current UTC year.
     */
    public function utcYear();

    /**
     * @return TimeZone The local timezone.
     */
    public function timeZone();

    /**
     * @return integer The current time as a unix timestamp.
     */
    public function unixTime();
}