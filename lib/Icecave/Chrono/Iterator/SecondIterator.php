<?php
namespace Icecave\Chrono\Iterator;

use Icecave\Chrono\DateTime;
use Icecave\Chrono\TimePointInterface;
use Icecave\Chrono\TimeSpan\Period;
use Icecave\Chrono\TypeCheck\TypeCheck;

class SecondIterator extends TimeSpanIterator
{
    /**
     * @param TimePointInterface $startTime  The time to start iterating at.
     * @param integer|null       $iterations The number of iterations or null to iterate forever.
     */
    public function __construct(TimePointInterface $startTime, $iterations)
    {
        $this->typeCheck = TypeCheck::get(__CLASS__, func_get_args());

        parent::__construct(
            $startTime,
            new Period(0, 0, 0, 0, 0, 1),
            $iterations
        );
    }

    /**
     * @return DateTime The current iteration date time.
     */
    public function current()
    {
        $this->typeCheck->current(func_get_args());

        $timePoint = parent::current();

        return new DateTime(
            $timePoint->year(),
            $timePoint->month(),
            $timePoint->day(),
            $timePoint->hours(),
            $timePoint->minutes(),
            $timePoint->seconds(),
            $timePoint->timeZone()
        );
    }

    private $typeCheck;
}
