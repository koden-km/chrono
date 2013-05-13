<?php
namespace Icecave\Chrono\Interval;

use Icecave\Chrono\Date;
use PHPUnit_Framework_TestCase;

class MonthTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->year = new Year(2012);
        $this->month = new Month($this->year, 2);
    }

    public function testCreateFromTimePoint()
    {
        $month = Month::fromTimePoint(new Date(2012, 12, 30));
        $this->assertInstanceOf(__NAMESPACE__ . '\Month', $month);
        $this->assertSame(2012, $month->year()->ordinal());
        $this->assertSame(12, $month->ordinal());
    }

    public function testYear()
    {
        $this->assertSame($this->year, $this->month->year());
    }

    public function testOrdinal()
    {
        $this->assertSame(2, $this->month->ordinal());
    }

    public function testStart()
    {
        $this->assertEquals(new Date(2012, 2, 1), $this->month->start());
    }

    public function testEnd()
    {
        $this->assertEquals(new Date(2012, 3, 1), $this->month->end());
    }

    public function testNumberOfDays()
    {
        $this->assertEquals(29, $this->month->numberOfDays());
    }

    public function testIsoString()
    {
        $this->assertEquals('2012-02', $this->month->isoString());
        $this->assertEquals('2012-02', $this->month->__toString());
    }
}
