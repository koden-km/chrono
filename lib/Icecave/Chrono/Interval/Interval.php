<?php
namespace Icecave\Chrono\Interval;

use Icecave\Chrono\DateTime;
use Icecave\Chrono\Iso8601Interface;
use Icecave\Chrono\Support\Iso8601;
use Icecave\Chrono\TimePointInterface;
use Icecave\Chrono\TimeSpan\Period;
use Icecave\Chrono\TypeCheck\TypeCheck;
use InvalidArgumentException;

/**
 * An interval represents a stretch of time between two known time points.
 */
class Interval extends AbstractInterval implements Iso8601Interface
{
    /**
     * @param TimePointInterface $start The start of the interval.
     * @param TimePointInterface $end   The start of the interval.
     */
    public function __construct(TimePointInterface $start, TimePointInterface $end)
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        if ($start->isGreaterThan($end)) {
            throw new InvalidArgumentException('Start point must not be greater than end point.');
        }

        $this->start = $start;
        $this->end = $end;

        parent::__construct();
    }

    /**
     * Standard interval formats:
     *   <start datetime>/<end datetime>
     *   <start datetime>/<duration>
     *   <duration>/<end datetime>
     *   <duration>
     *
     * @link http://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     *
     * Note: Duration only format is not supported.
     *
     * @param string $isoString A string containing an interval in any ISO-8601 compatible interval format.
     *
     * @return Interval The Interval constructed from the ISO compatible string.
     */
    public static function fromIsoString($isoString)
    {
        TypeCheck::get(__CLASS__)->fromIsoString(func_get_args());

        $result = Iso8601::parseInterval($isoString);
        $type = $result['type'];
        $interval = $result['interval'];

        if ($type === 'duration/datetime') {
            list($duration, $end) = $interval;
            $period = Period::fromIsoString($duration);
            $end = DateTime::fromIsoString($end);
            $start = $period->inverse()->resolveToTimePoint($end);
        } elseif ($type === 'datetime/duration') {
            list($start, $duration) = $interval;
            $start = DateTime::fromIsoString($start);
            $period = Period::fromIsoString($duration);
            $end = $period->resolveToTimePoint($start);
        } else {
            list($start, $end) = $interval;
            $start = DateTime::fromIsoString($start);
            $end = DateTime::fromIsoString($end);
        }

        return new self($start, $end);
    }

    /**
     * @return TimePointInterface The start of the interval.
     */
    public function start()
    {
        $this->typeCheck->start(func_get_args());

        return $this->start;
    }

    /**
     * @return TimePointInterface The end of the interval.
     */
    public function end()
    {
        $this->typeCheck->end(func_get_args());

        return $this->end;
    }

    /**
     * @return string A string representing this object in an ISO compatible format (YYYY-MM-DDThh:mm:ss[+-]hh:mm/PnYnMnDTnHnMnS).
     */
    public function isoStringWithDuration()
    {
        $this->typeCheck->isoStringWithDuration(func_get_args());

        $start = $this->start();
        $start = Iso8601::formatDateTime(
            $start->year(),
            $start->month(),
            $start->day(),
            $start->hours(),
            $start->minutes(),
            $start->seconds(),
            $start->timeZone()->isoString()
        );

        return $start . '/' . $this->duration()->isoString();
    }

    /**
     * @return string A string representing this object in an ISO compatible format (YYYY-MM-DDThh:mm:ss[+-]hh:mm/YYYY-MM-DDThh:mm:ss[+-]hh:mm).
     */
    public function isoString()
    {
        $this->typeCheck->isoString(func_get_args());

        $start = $this->start();
        $start = Iso8601::formatDateTime(
            $start->year(),
            $start->month(),
            $start->day(),
            $start->hours(),
            $start->minutes(),
            $start->seconds(),
            $start->timeZone()->isoString()
        );

        $end = $this->end();
        $end = Iso8601::formatDateTime(
            $end->year(),
            $end->month(),
            $end->day(),
            $end->hours(),
            $end->minutes(),
            $end->seconds(),
            $end->timeZone()->isoString()
        );

        return $start . '/' . $end;
    }

    /**
     * @return string A string representing this object in an ISO compatible format (YYYY-MM-DDThh:mm:ss[+-]hh:mm/YYYY-MM-DDThh:mm:ss[+-]hh:mm).
     */
    public function __toString()
    {
        return $this->isoString();
    }

    private $typeCheck;
    private $start;
    private $end;
}
