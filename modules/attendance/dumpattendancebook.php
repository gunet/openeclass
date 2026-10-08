<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
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

/*
Pitfalls regarding Excel spreadsheets:
1) Two worksheets cannot have the same name, regardless of upper or lowercase. Excel will return the error: Cannot rename a sheet to the same name as another sheet, a referenced object library or a workbook referenced by Visual Basic.
1.i) Meaning that if we have an illegal name such as `Test!` or `Test?` and we strip the suffix, they'll end up having the same name.
2) A worksheet name cannot be left blank.
3) A worksheet cannot be named history, in either lowercase or uppercase. History is reserved by Excel for tracking changes between shared workbooks. There could be a course named History.
4) The apostrophe (') cannot be used at the beginning or end of a worksheet name, but can be used in the middle of a name.
5) The following characters are forbidden for use when naming worksheets. Excel will not even allow you to type them:    \ / ? * [ ] :
6) Due to us preprocessing the worksheet titles, we need to add a proper title at the top of each worksheet that contains the original name with no modifications.

Sources:
https://github.com/PHPOffice/PhpSpreadsheet/issues/1024
https://github.com/PHPOffice/PhpSpreadsheet/blob/540dfb463ac9850519e9e3bbddc4013b378cb3d0/src/PhpSpreadsheet/Worksheet/Worksheet.php#L68
*/

const DUPLICATE_FORMAT_STR = ' (%u)';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalFormatValueObject;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalIconSet;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\IconSetValues;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\CellRange;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\AddressRange;
use PhpOffice\PhpSpreadsheet\Cell\ColumnRange;
use PhpOffice\PhpSpreadsheet\Cell\RowRange;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use OndrejVrto\FilenameSanitize\FilenameSanitize;

$require_current_course = true;
$require_editor = true;

require_once '../../include/baseTheme.php';

// Value binder for PhpSpreadsheet to force columns as strings
class AttendanceBookBinder implements PhpOffice\PhpSpreadsheet\Cell\IValueBinder {
    // Encapsulate a StringValueBinder to force the requested range as strings.
    private StringValueBinder $svb;
    private DefaultValueBinder $dvb;
    private array $ranges;

    function __construct(array $ranges) {
        $this->svb = new StringValueBinder();
        $this->svb->setNumericConversion(false)
            ->setSetIgnoredErrors(true)
            ->setBooleanConversion(false)
            ->setNullConversion(false)
            ->setFormulaConversion(false);

        $this->dvb = new DefaultValueBinder();

        $this->ranges = $ranges;
    }

    public function setStringRanges(array $ranges) {
        $this->ranges = $ranges;
    }

    public function getStringRanges(): array {
        return $this->ranges;
    }

    public function bindValue(Cell $cell, mixed $value): bool {
        foreach (($this->ranges ?? []) as $range) {
            if ($range instanceof AddressRange && $cell->isInRange($range)) {
                return $this->svb->bindValue($cell, $value);
            }
        }
        return $this->dvb->bindValue($cell, $value);
    }
}

if (!isset($_GET['course'])) {
    Session::flash('message', $langGeneralError);
    Session::flash('alert-class', 'alert-error');
    redirect_to_home_page('/main/portfolio.php');
}

$attendance_id = isset($_GET['attendance_id']) ? getDirectReference($_GET['attendance_id']) : NULL;
if ($attendance_id === NULL) {
    Session::flash('message', $langGeneralError);
    Session::flash('alert-class', 'alert-error');
    redirect_to_home_page('/modules/attendance/index.php?course=' . urlencode($_GET['course']));
}

$attendance_book_title = Database::get()->querySingle("SELECT title FROM attendance WHERE id = ?d", $attendance_id)->title ?? $langAttendance;

// The items to the left of each attendance activity in the header
$header_left = [ $langAm, $langSurname, $langName, $langUsername, $langEmail ];
// To the right of it
$header_right = [ $langAttendanceAbsences ];
$attendanceBookBinder = new AttendanceBookBinder([ColumnRange::fromColumnIndexes(1, count($header_left)), new RowRange(1)]);

$spreadsheet = new Spreadsheet();
$spreadsheet->setValueBinder($attendanceBookBinder);

$firstsheet = $sheet = $spreadsheet->getActiveSheet();

$course_title = course_id_to_title($course_id);

// Fallback in case something goes wrong
$filename = "attendance_export.xlsx";

$spreadsheet->getProperties()->setTitle("{$langAttendance} {$course_title}")
    ->setSubject('{$langAttendance} {$course_title}')
    ->setDescription("{$langAttendance} {$course_title}")
    ->setKeywords("{$langAttendance}")
    ->setCustomProperty("CourseTitle", $course_title)
    ->setCustomProperty("CourseCode", $course_code)
    ->setCustomProperty("AttendanceId", $attendance_id);

$attendance_activity_title = '';
$attendance_limit = 0;

// Check if we are exporting a specific activity (for re-importing) and handle it accordingly
// All further checks for isset($_GET['activity_id']) are for the same purpose
if (isset($_GET['activity_id'])) {
    $activity_id = $_GET['activity_id'];
    $spreadsheet->getProperties()->setCustomProperty("OpeneClassExportVer", 1);
    // TODO: In the future this can be used to check if the user is importing the file to the correct attendance book
    $attendances_array = Database::get()->queryArray("SELECT attendance_activities.id, attendance_book.uid, attendance_activities.title, attendance_book.attend, attendance_activities.date FROM attendance_activities LEFT JOIN attendance_book ON attendance_activities.id = attendance_book.attendance_activity_id WHERE attendance_activities.attendance_id = ?d AND attendance_activities.id = ?d ORDER BY ISNULL(attendance_activities.date), attendance_activities.date;", $attendance_id, $activity_id);
    if($attendances_array) {
        $spreadsheet->getProperties()->setCustomProperty("ActivityId", $activity_id);
        $attendance_activity_title = $attendances_array[0]->title;
        if (strlen($attendance_activity_title) === 0) {
            $attendance_activity_title = $langGradebookNoTitle;
        }

        $spreadsheet->getProperties()->setCustomProperty("ActivityTitle", $attendance_activity_title);
    }
} else {
    $attendances_array = Database::get()->queryArray("SELECT attendance_activities.id, attendance_book.uid, attendance_activities.title, attendance_book.attend, attendance_activities.date FROM attendance_activities LEFT JOIN attendance_book ON attendance_activities.id = attendance_book.attendance_activity_id WHERE attendance_activities.attendance_id = ?d ORDER BY ISNULL(attendance_activities.date), attendance_activities.date;", $attendance_id);
    $attendance_limit = Database::get()->querySingle("SELECT `limit` FROM attendance WHERE id = ?d", $attendance_id)->limit;
}

$filename = FilenameSanitize::of("{$course_code}_{$attendance_book_title}" . (strlen($attendance_activity_title) === 0 ? '' : "_{$attendance_activity_title}"))->disableLowerCase()->addActualExtensionToFilename()->withNewExtension('xlsx')->get();

$students_array = Database::get()->queryArray("SELECT user.id, user.username, user.surname, user.givenname, user.email, user.am FROM user INNER JOIN attendance_users ON user.id = attendance_users.uid WHERE attendance_users.attendance_id = ?d ORDER BY surname", $attendance_id);

// Explicitly check for NULL for failed queries
// We don't want an error if either array happens to be empty due to no data
if ($attendances_array === NULL || $students_array === NULL) {
    Session::flash('message', $langGeneralError);
    Session::flash('alert-class', 'alert-error');
    redirect_to_home_page('/modules/attendance/index.php?course=' . urlencode($_GET['course']) . '&attendance_id=' . urlencode($attendance_id));
}

// Get unique attendance activities
$attendances = [];
foreach ($attendances_array as $attendance) {
    if (!array_key_exists($attendance->id, $attendances))
        $attendances[$attendance->id] = ['title' => $attendance->title, 'entries' => [], 'date' => DateTime::createFromFormat('Y-m-d H:i:s', $attendance->date ?? '')];
    if ($attendance->attend && $attendance->uid)
            $attendances[$attendance->id]['entries'][$attendance->uid] = null;
}

// Create conditional for the attendance checkmarks
$cond = new Conditional();
$cond->setConditionType(Conditional::CONDITION_ICONSET);
$iconSet = new ConditionalIconSet();
$cond->setIconSet($iconSet);
$iconSet->setIconSetType(IconSetValues::ThreeSymbols)->setCfvos([
    new ConditionalFormatValueObject('num', 0),
    new ConditionalFormatValueObject('num', 1),
    new ConditionalFormatValueObject('num', 1)
]);
$iconSet->setShowValue(false);

// Create conditional to set the FG colour to white
$cond_w = new Conditional();
$cond_w->setConditionType(Conditional::CONDITION_CELLIS)
    ->setOperatorType(Conditional::OPERATOR_BETWEEN)
    ->addCondition('0')
    ->addCondition('1');

// Set the foreground colour as background for the conditional to hide the numbers visible next to the icons
// due to a bug in LibreOffice Calc (https://bugs.documentfoundation.org/show_bug.cgi?id=163998)
$cond_w->getStyle()->getFont()->getColor()->setARGB($cond_w->getStyle()->getFont()->getColor()->getARGB());

// When exporting the entire attendance book, write the summary worksheet
if (!isset($_GET['activity_id'])) {
    // Attendance limit conditional - greater or equal to 0 means that the student's attendance ratio was satisfactory
    $cond_l = new Conditional();
    $cond_l->setConditionType(Conditional::CONDITION_ICONSET);
    $iconSet = new ConditionalIconSet();
    $cond_l->setIconSet($iconSet);
    $iconSet->setIconSetType(IconSetValues::ThreeSymbols)->setCfvos([
        new ConditionalFormatValueObject('num', 0),
        new ConditionalFormatValueObject('num', 0),
        new ConditionalFormatValueObject('num', $attendance_limit)
    ]);

    $sheet->setTitle($langSynopsis);

    // Write the header (left + activities + right)
    // Fallback if the title was blank or consisted of illegal characters only
    if (strlen($attendance->title) === 0) {
        $title = $langAttendance;
    }

    // Show the column only if an attendance limit is set
    $header_right_summary = $header_right;
    if ($attendance_limit > 0) {
        $header_right_summary[] = $langAttendanceLimitTitle;
    }

    $header = array_merge($header_left, array_map(fn($v):string => strlen($v['title']) === 0 ? $langGradebookNoTitle : $v['title'], $attendances), $header_right_summary);

    // Go through each student
    $rows = [$header];
    foreach ($students_array as $student) {
        // Fill in the student information (corresponding to $header_left)
        $row = [$student->am, $student->surname, $student->givenname, $student->username, $student->email];

        // Then, fill in the attendance information for the student
        foreach ($attendances as &$attendance) {
            $attended = intval(array_key_exists($student->id, $attendance['entries']));
            $row[] = $attended;
        }
        unset($attendance);

        // Fill in the data for $header_right_summary
        // Add the sum formula
        $sum_range = new CellRange(CellAddress::fromColumnAndRow(1 + count($header_left), 1 + count($rows)), CellAddress::fromColumnAndRow(count($header_left) + count($attendances), 1 + count($rows)));
        $row[] = "=COUNTIF({$sum_range}, \"> 0\")";
        // Attendance limit
        $row[] = "=MAX(0, SUM({$attendance_limit}, -COUNTIF({$sum_range}, \"> 0\")))";
        $rows[] = $row;
    }

    $sheet->fromArray($rows, NULL, 'A1', true);
    // Apply formatting
    // Autosize all columns, make headers bold and centered
    foreach ($sheet->getColumnIterator() as $column) {
        $i = $column->getColumnIndex();
        $sheet->getStyle("{$i}1")->getFont()->setBold(true);
        $sheet->getStyle("{$i}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension($i)->setAutoSize(true);
    }

    // Rotate attendance book names as they can be very long, force align left (bottom)
    for ($i = 1 + count($header_left); $i <= count($header_left) + count($attendances); $i++) {
        $sheet->getStyle([$i, 1])->getAlignment()->setTextRotation(90)->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Make sure text is not parsed as formulae
    foreach ($attendanceBookBinder->getStringRanges() as $range) {
        $sheet->getStyle($range)->setQuotePrefix(true);
    }

    for ($j = 2; $j <= count($students_array) + 1; $j++) {
        for ($i = 1 + count($header_left); $i <= count($header_left) + count($attendances); $i++) {
            $sheet->getStyle([$i, $j])->setConditionalStyles([$cond, $cond_w]);
        }
        if ($attendance_limit > 0) {
            $sheet->getStyle([1 + count($header_left) + count($attendances), $j])->setConditionalStyles([$cond_l]);
        }
    }

    $sheet->freezePane("{$sheet->getHighestColumn()}2");
}

// Preprocess the worksheet titles to remove special characters
foreach ($attendances as &$attendance) {
    $title = trim($attendance['title']);
    if (strlen($title) === 0) {
        $title = $langGradebookNoTitle;
    }
    // Remove illegal characters
    // Even though Excel allows single quotes anywhere but the start or end of the string
    // let's simplify the code by removing them entirely
    $title = str_replace(array_merge(Worksheet::getInvalidCharacters(), ['\'']), '', $title);

    // Fallback if the title was blank or consisted of illegal characters only
    if (strlen($title) === 0) {
        $title = $langAttendance;
    }
    // Trim to 31 characters
    $title = mb_substr($title, 0, Worksheet::SHEET_TITLE_MAXIMUM_LENGTH, 'UTF-8');
    $attendance['excel_safe_title'] = $title;
}
unset($attendance);

// Count the number of duplicates case-insensitively
$duplicates = array_count_values(array_map(fn($x) => mb_strtoupper($x['excel_safe_title'], 'UTF-8'), $attendances));

// Add an extra field so we can keep track of how many duplicates we have come across so far
$duplicates = array_map(fn($g) => ['count' => $g, 'cur' => 0], $duplicates);

// For each attendance book, write new sheet
foreach ($attendances as &$attendance) {
    // Handle duplicate titles here
    $final_title = $attendance['excel_safe_title'];
    // We assume the keys exist
    $final_title_ref = &$duplicates[mb_strtoupper($final_title, 'UTF-8')];
    if ($final_title_ref['count'] > 1) {
        // Find out how many characters the maximum duplicate will require
        $len = mb_strlen(sprintf(DUPLICATE_FORMAT_STR, $final_title_ref['count'] - 1));
        // Then trim the title accordingly
        $final_title = mb_substr($final_title, 0, Worksheet::SHEET_TITLE_MAXIMUM_LENGTH - $len);
        // Append the current duplicate number and increment it
        if ($final_title_ref['cur']++ > 0) {
            $final_title .= sprintf(DUPLICATE_FORMAT_STR, $final_title_ref['cur']);
        }
    }

    // Excel does not allow a worksheet to be named "history"
    // We check this down here to avoid duplicates named "History (1) (2)"
    if (mb_strtoupper($final_title, 'UTF-8') === 'HISTORY') {
        $final_title .= ' (1)';
    }

    $pre_header_data = [[$langName, $attendance['title']], [$langDate, $attendance['date'] === false ? '-' : SharedDate::dateTimeToExcel($attendance['date'])]];

    // Style pre-header data
    for ($i = 1; $i <= count($pre_header_data); $i++) {
        $sheet->getStyle("A{$i}")->getFont()->setBold(true);
    }

    // Pre-header + empty row + header
    $rows = array_merge($pre_header_data, [[], array_merge($header_left, $header_right)]);

    // It is located at $header_right[0]
    $attendance_col = count($rows[3]);

    // Start of main header
    $header_row = count($rows);

    // Start of actual student data
    $offset = $header_row + 1;

    foreach ($students_array as $student) {
        $row = [$student->am, $student->surname, $student->givenname, $student->username, $student->email];
        $attended = intval(array_key_exists($student->id, $attendance['entries']));
        $row[] = $attended;
        $rows[] = $row;
    }

    // Create the sheet
    $sheet = new Worksheet($spreadsheet);
    $sheet->setTitle($final_title);
    $spreadsheet->addSheet($sheet);

    $total_attendances_pos = $offset + count($students_array);

    if (count($students_array)) {
        // We need this to correctly position the student data
        // We do this here so that it doesn't affect the `else` case, in which case we have no data
        $total_attendances_pos--;

        // Offset the string value binder by the correct amount of rows and exclude the last column (attendance 0/1)
        // Also add the first row as it contains $final_title and it should be a string
        assert($rows[0][0] == $langName);
        $attendanceBookBinder->setStringRanges([new CellRange(CellAddress::fromColumnAndRow(1, $offset), CellAddress::fromColumnAndRow($attendance_col - 1, $total_attendances_pos)),
            new RowRange(1)]);
    } else {
        // Unless we have no students, in which case we do not want the binder to do anything special
        // So only add the first row which contains the name (as above in the `if` block)
        $attendanceBookBinder->setStringRanges([new RowRange(1)]);
    }

    $sheet->fromArray($rows, NULL, 'A1', true);
    foreach ($sheet->getColumnIterator() as $column) {
        // Autosize all columns, make headers bold and centered
        $i = $column->getColumnIndex();
        $sheet->getStyle("{$i}{$header_row}")->getFont()->setBold(true);
        $sheet->getStyle("{$i}{$header_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getColumnDimension($i)->setAutoSize(true);
    }

    // Make sure text is not parsed as formulae
    foreach ($attendanceBookBinder->getStringRanges() as $range) {
        $sheet->getStyle($range)->setQuotePrefix(true);
    }

    // All attendances are in a single column
    for ($j = $offset; $j < $offset + count($students_array); $j++)
        $sheet->getStyle([$attendance_col, $j])->setConditionalStyles([$cond, $cond_w]);

    // Add sum to the end of the conditionals
    // The sum range is (Foffset, Fcount($students_array))
    // If we are printing an empty sheet (no students), then we must use the first available row for the hardcoded 0 sum
    // We also can't use COUNTIF because there are no rows to count, so the range will be invalid
    if (count($students_array) === 0) {
        $start_cell = $sheet->getCell([$attendance_col - 1, $total_attendances_pos]);
        $sum_cell = [[$langTotal, 0]];
    } else {
        $start_cell = $sheet->getCell([$attendance_col - 1, $total_attendances_pos + 1]);
        $range = new CellRange(CellAddress::fromColumnAndRow($attendance_col, $offset), CellAddress::fromColumnAndRow($attendance_col, $total_attendances_pos));
        $sum_cell = [[$langTotal, "=COUNTIF({$range}, \"> 0\")"]];
    }

    $coord = $start_cell->getCoordinate();
    $sheet->fromArray($sum_cell, NULL, $coord, true);
    // Make sum bold
    $sheet->getStyle($coord)->getFont()->setBold(true);

    // Set the appropriate format for the date cell
    assert($rows[1][0] == $langDate);
    $sheet->getStyle('B2')->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');

}
unset($attendance);

// HACK: If we are exporting a single activity, remove the default/first worksheet as it is now blank
if (isset($_GET['activity_id'])) {
    $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($firstsheet));
}

// Set the first sheet as the default
$spreadsheet->setActiveSheetIndex(0);

// Write file to output
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
set_content_disposition('attachment', $filename);
header('Cache-Control: max-age=0');
try {
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
} catch (Exception $e) {
    error_log($e);
    Session::flash('message', $langGeneralError);
    Session::flash('alert-class', 'alert-error');
    redirect_to_home_page('/modules/attendance/index.php?course=' . urlencode($_GET['course']) . '&attendance_id=' . urlencode($attendance_id));
}
