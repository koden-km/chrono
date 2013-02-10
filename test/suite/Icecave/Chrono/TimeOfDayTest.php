<?php
namespace Icecave\Chrono;

use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;

class TimeOfDayTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_time = new TimeOfDay(10, 20, 30);
    }

    public function testNormalization()
    {
        $time = new TimeOfDay(10, 20, 70);
        $this->assertSame('10:21:10+00:00', $time->isoString());
    }

    public function testHours()
    {
        $this->assertSame(10, $this->_time->hours());
    }

    public function testMinutes()
    {
        $this->assertSame(20, $this->_time->minutes());
    }

    public function testSeconds()
    {
        $this->assertSame(30, $this->_time->seconds());
    }

    public function testToTimeZone()
    {
        $timeZone = new TimeZone(36000);
        $result = $this->_time->toTimeZone($timeZone);

        $this->assertInstanceOf(__NAMESPACE__ . '\TimeOfDay', $result);
        $this->assertSame('20:20:30+10:00', $result->isoString());
    }

    public function testToTimeZoneSame()
    {
        $result = $this->_time->toTimeZone(new TimeZone);
        $this->assertSame($this->_time, $result);
    }

    public function testToUtc()
    {
        $timeZone = new TimeZone(36000);
        $time = new TimeOfDay(10, 20, 30, $timeZone);
        $result = $time->toUtc();

        $this->assertInstanceOf(__NAMESPACE__ . '\TimeOfDay', $result);
        $this->assertSame('00:20:30+00:00', $result->isoString());
    }

    public function testTimeZone()
    {
        $this->assertTrue($this->_time->timeZone()->isUtc());

        $timeZone = new TimeZone(36000, true);
        $time = new TimeOfDay(10, 20, 30, $timeZone);
        $this->assertSame($timeZone, $time->timeZone());
    }

    public function testOn()
    {
        $date = new Date(2013, 2, 1);
        $result = $this->_time->on($date);
        $expected = new DateTime(2013, 2, 1, 10, 20, 30);

        $this->assertEquals($expected, $result);
    }

    public function testOnWithTimeZoneCoversion()
    {
        $date = new Date(2013, 2, 1, new TimeZone(36000));
        $result = $this->_time->on($date);
        $expected = new DateTime(2013, 1, 31, 10, 20, 30);

        $this->assertEquals($expected, $result);
    }

    public function testCompareSelf()
    {
        $this->assertSame(0, $this->_time->compare($this->_time));
    }

    public function testCompareClone()
    {
        $time = clone $this->_time;
        $this->assertSame(0, $this->_time->compare($time));
    }

    public function testCompareTime()
    {
        $time = new TimeOfDay(10, 20, 31);
        $this->assertLessThan(0, $this->_time->compare($time));
        $this->assertGreaterThan(0, $time->compare($this->_time));

        $time = new TimeOfDay(10, 21, 30);
        $this->assertLessThan(0, $this->_time->compare($time));
        $this->assertGreaterThan(0, $time->compare($this->_time));

        $time = new TimeOfDay(11, 20, 30);
        $this->assertLessThan(0, $this->_time->compare($time));
        $this->assertGreaterThan(0, $time->compare($this->_time));
    }

    public function testCompareTimeZone()
    {
        $time = new TimeOfDay(10, 20, 30, new TimeZone(36000));
        $this->assertLessThan(0, $this->_time->compare($time));
        $this->assertGreaterThan(0, $time->compare($this->_time));
    }

    public function testTotalSeconds()
    {
        $this->assertSame(37230, $this->_time->totalSeconds());
    }

    public function testFormat()
    {
        $formatter = Phake::mock(__NAMESPACE__ . '\Format\FormatterInterface');
        Liberator::liberateClass(__NAMESPACE__ . '\Format\DefaultFormatter')->instance = $formatter;

        Phake::when($formatter)
            ->formatTimeOfDay(Phake::anyParameters())
            ->thenReturn('<1st>')
            ->thenReturn('<2nd>');

        $result = $this->_time->format('H:i:s');
        $this->assertSame('<1st>', $result);

        $result = $this->_time->format('H:i:s', $formatter);
        $this->assertSame('<2nd>', $result);

        Phake::verify($formatter, Phake::times(2))->formatTimeOfDay($this->_time, 'H:i:s');
    }

    public function testIsoString()
    {
        $this->assertEquals('10:20:30+00:00', $this->_time->isoString());
        $this->assertEquals('10:20:30+00:00', $this->_time->__toString());
    }

    /**
     * @dataProvider validIsoStrings
     */
    public function testFromIsoString($isoString, $expected)
    {
        $result = TimeOfDay::fromIsoString($isoString);
        $this->assertSame($expected, $result->isoString());
    }

    public function validIsoStrings()
    {
        return array(
            'Basic'    => array('102030',   '10:20:30+00:00'),
            'Extended' => array('10:20:30', '10:20:30+00:00'),
        );
    }

    /**
     * @dataProvider validIsoStringsWithTimeZone
     */
    public function testFromIsoStringWithTimeZone($isoString, $expectedString, $expectedTimeZone)
    {
        $result = TimeOfDay::fromIsoString($isoString);
        $this->assertSame($expectedString, $result->isoString());
        $this->assertEquals($expectedTimeZone, $result->timeZone());
    }

    public function validIsoStringsWithTimeZone()
    {
        $hours = 60 * 60;
        $minutes = 60;

        $timeZoneUTC = new TimeZone(0);
        $timeZonePos1100 = new TimeZone(11 * $hours);
        $timeZonePos1122 = new TimeZone((11 * $hours) + (22 * $minutes));
        $timeZoneNeg1100 = new TimeZone(-(11 * $hours));
        $timeZoneNeg1122 = new TimeZone(-((11 * $hours) + (22 * $minutes)));

        return array(
            'Basic, UTC'               => array('102030Z',        '10:20:30+00:00', $timeZoneUTC),
            'Basic, positive short'    => array('102030+11',      '10:20:30+11:00', $timeZonePos1100),
            'Basic, positive long'     => array('102030+1122',    '10:20:30+11:22', $timeZonePos1122),
            'Basic, negative short'    => array('102030-11',      '10:20:30-11:00', $timeZoneNeg1100),
            'Basic, negative long'     => array('102030-1122',    '10:20:30-11:22', $timeZoneNeg1122),
            'Extended, UTC'            => array('10:20:30Z',      '10:20:30+00:00', $timeZoneUTC),
            'Extended, positive short' => array('10:20:30+11',    '10:20:30+11:00', $timeZonePos1100),
            'Extended, positive long'  => array('10:20:30+11:22', '10:20:30+11:22', $timeZonePos1122),
            'Extended, negative short' => array('10:20:30-11',    '10:20:30-11:00', $timeZoneNeg1100),
            'Extended, negative long'  => array('10:20:30-11:22', '10:20:30-11:22', $timeZoneNeg1122),
        );
    }

    /**
     * @dataProvider invalidIsoStrings
     */
    public function testFromIsoStringWithInvalidIsoDateTime($isoString, $expected)
    {
        $this->setExpectedException('InvalidArgumentException', $expected);
        TimeOfDay::fromIsoString($isoString);
    }

    public function invalidIsoStrings()
    {
        return array(
            'Not enough digits'                  => array('1',          'Invalid ISO time: "1"'),
            'Not enough digits'                  => array('00000',      'Invalid ISO time: "00:00:0"'),
            'Not enough digits'                  => array('11223',      'Invalid ISO time: "00:00:0"'),
            'Not enough digits'                  => array('00:00:0',    'Invalid ISO time: "00:00:0"'),
            'Not enough digits'                  => array('11:22:3',    'Invalid ISO time: "11:22:3"'),
            'Too many digits, invalid time zone' => array('1122334',    'Invalid ISO time: "4"'),
            'Too many digits, invalid time zone' => array('11:22:33:4', 'Invalid ISO time zone: ":4"'),
            'Missing minute and second'          => array('11',         'Invalid ISO time: "11"'),
            'Missing second'                     => array('1122',       'Invalid ISO time: "11:22"'),
            'Missing second'                     => array('11:22',      'Invalid ISO time: "11:22"'),
            'Unexpected prefix'                  => array('-10:20:30',  'Invalid ISO time: "-10:20:30"'),
            'Invalid format'                     => array('11:',        'Invalid ISO time: "11:"'),
            'Invalid format'                     => array('11:22:',     'Invalid ISO time: "11:22:"'),
            'Invalid letters'                    => array('AABBCC',     'Invalid ISO time: "AABBCC"'),
            'Invalid letters'                    => array('AA:BB:CC',   'Invalid ISO time: "AA:BB:CC"'),
            'Invalid letters'                    => array('AA:22:33',   'Invalid ISO time: "AA:22:33"'),
            'Invalid letters'                    => array('11:BB:33',   'Invalid ISO time: "11:BB:33"'),
            'Invalid letters'                    => array('11:22:CC',   'Invalid ISO time: "11:22:CC"'),
            'Invalid separator'                  => array('11-22-33',   'Invalid ISO time: "11-22-33"'),
            'Missing time'                       => array('+10',        'Invalid ISO time: "+10"'),
            'Missing time'                       => array('+10:20',     'Invalid ISO time: "+10:20"'),
        );
    }
}