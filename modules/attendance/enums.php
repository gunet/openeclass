<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2025, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

interface IEnumSemester
{
    public function str(): string;
    public function month_start(): int;
    // Used to calculate the academic year
    public function year_offset() : DateInterval;
    public static function fromMonth(int $month) : self;
}

enum Semester: int implements IEnumSemester
{
    case WINTER = 1;
    case SPRING = 2;
    public function str(): string
    {
        return match($this) {
            Semester::WINTER => 'winter',
            Semester::SPRING => 'spring',
        };
    }

    public function month_start(): int
    {
        return match($this) {
            Semester::WINTER => 9,
            Semester::SPRING => 3,
        };
    }

    public function year_offset(): DateInterval
    {
        return match($this) {
            Semester::WINTER => DateInterval::createFromDateString('0 days'),
            Semester::SPRING => DateInterval::createFromDateString('-1 year'),
        };
    }

    public static function fromMonth(int $month): self
    {
        return ($month >= self::SPRING->month_start() && $month < self::WINTER->month_start() ? self::SPRING : self::WINTER);
    }
}
?>
