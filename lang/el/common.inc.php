<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/* * ********************************************************
 * General Messages - Feel free to suit them in your needs
 * ******************************************************** */
$langEclass = "Πλατφόρμα Τηλεκπαίδευσης";

$langTeacher = "Εκπαιδευτής";
$langOfTeacher = "Εκπαιδευτή";
$langTeachers = "Εκπαιδευτές";
$langOfTeachers = "Εκπαιδευτών";

$langsTeacher = "εκπαιδευτής";
$langsOfTeacher = "εκπαιδευτή";
$langsTeachers = "εκπαιδευτές";
$langsOfTeachers = "εκπαιδευτών";
$langCTeacher = "ΕΚΠΑΙΔΕΥΤΗΣ";

$langCourseAdmin = "Διαχειριστής";
$langOfCourseAdmin = "Διαχειριστή";
$langCourseAdmins = "Διαχειριστές";
$langOfCourseAdmins = "Διαχειριστών";

$langsCourseAdmin = "διαχειριστής";
$langsOfCourseAdmin = "διαχειριστή";
$langsCourseAdmins = "διαχειριστές";
$langsOfCourseAdmins = "διαχειριστών";

$langCourseAdminTeacher = "Διαχειριστής - Εκπαιδευτής";
$langOfCourseAdminTeacher = "Διαχειριστή - Εκπαιδευτή";
$langCourseAdminTeachers = "Διαχειριστές - Εκπαιδευτές";
$langOfCourseAdminTeachers = "Διαχειριστών - Εκπαιδευτών";

$langsCourseAdminTeacher = "διαχειριστής - εκπαιδευτής";
$langsOfCourseAdminTeacher = "διαχειριστή - εκπαιδευτή";
$langsCourseAdminTeachers = "διαχειριστές - εκπαιδευτές";
$langsOfCourseAdminTeachers = "διαχειριστών - εκπαιδευτών";

$langEditor = "Βοηθός εκπαιδευτή";
$langOfEditor = "Βοηθού εκπαιδευτή";
$langsEditor = "βοηθός εκπαιδευτή";
$langsOfEditor = "βοηθού εκπαιδευτή";

$langCourseReviewer = "Επόπτης";
$langOfCourseReviewer = "Επόπτη";
$langsCourseReviewer = "επόπτης";
$langsOfCourseReviewer = "επόπτη";

$langGroupTutor = "Υπεύθυνος ομάδας";
$langsOfGroupTutor = "υπεύθυνου ομάδας";

$langStudent = "Εκπαιδευόμενος";
$langOfStudent = "Εκπαιδευόμενου";
$langStudents = "Εκπαιδευόμενοι";
$langOfStudents = "Εκπαιδευόμενων";
$langCStudent = "ΕΚΠΑΙΔΕΥΟΜΕΝΟΣ";
$langCStudent2 = "ΜΑΘΗΤΗΣ";

$langsStudent = "εκπαιδευόμενος";
$langsOfStudent = "εκπαιδευόμενου";
$langsStudents = "εκπαιδευόμενοι";
$langsOfStudents = "εκπαιδευόμενων";
$langsOfStudentss = "εκπαιδευόμενους";
$langsstudent_acc = "εκπαιδευόμενο";

$langGuest = "Χρήστης Επισκέπτης";
$langGuests = "Χρήστες Επισκέπτες";

$langCourse = "Μάθημα";
$langCourses = "Μαθήματα";
$langOfCourses = "Μαθημάτων";
$langOfCourse = "Μαθήματος";

$langsCourse = "μάθημα";
$langsCourses = "μαθήματα";
$langsOfCourse = "μαθήματος";
$langsOfCourses = "μαθημάτων";

$langFaculty = "Κατηγορία";
$langOfFaculty = "Κατηγορίας";
$langOfFaculties = "Κατηγοριών";
$langFaculties = "Κατηγορίες";

$langsFaculty = "κατηγορία";
$langsOfFaculty = "κατηγορίας";
$langsFaculties = "κατηγορίες";

$langpre = "Προπτυχιακό";
$langpost = "Μεταπτυχιακό";
$langother = "Άλλο";

$langInfoAbout = "Η πλατφόρμα $siteName αποτελεί ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Μαθημάτων. Ακολουθεί τη φιλοσοφία του λογισμικού ανοικτού κώδικα και υποστηρίζει την υπηρεσία Ασύγχρονης Τηλεκπαίδευσης χωρίς περιορισμούς και δεσμεύσεις. Η πρόσβαση στην υπηρεσία γίνεται με τη χρήση ενός απλού φυλλομετρητή (web browser) χωρίς την απαίτηση εξειδικευμένων τεχνικών γνώσεων.
";
$langExtrasLeft = '';
$langExtrasRight = '';



if (file_exists('config/config.php')) {
    if(get_config('show_always_collaboration') and get_config('show_collaboration')){

        $langEclass = "Πλατφόρμα Συνεργασίας";

        $langTeacher = "Συντονιστής";
        $langOfTeacher = "Συντονιστή";
        $langTeachers = "Συντονιστές";
        $langOfTeachers = "Συντονιστών";

        $langsTeacher = "συντονιστής";
        $langsOfTeacher = "συντονιστή";
        $langsTeachers = "συντονιστές";
        $langsOfTeachers = "συντονιστών";
        $langCTeacher = "ΣΥΝΤΟΝΙΣΤΗΣ";

        $langCourseAdminTeacher = "Διαχειριστής - Συντονιστής";
        $langOfCourseAdminTeacher = "Διαχειριστή - Συντονιστή";
        $langCourseAdminTeachers = "Διαχειριστές - Συντονιστές";
        $langOfCourseAdminTeachers = "Διαχειριστών - Συντονιστών";

        $langsCourseAdminTeacher = "διαχειριστής - συντονιστής";
        $langsOfCourseAdminTeacher = "διαχειριστή - συντονιστή";
        $langsCourseAdminTeachers = "διαχειριστές - συντονιστές";
        $langsOfCourseAdminTeachers = "διαχειριστών - συντονιστών";

        $langEditor = "Βοηθός συντονιστή";
        $langOfEditor = "Βοηθού συντονιστή";
        $langsEditor = "βοηθός συντονιστή";
        $langsOfEditor = "βοηθού συντονιστή";

        $langStudent = "Μέλος";
        $langOfStudent = "Μέλους";
        $langStudents = "Μέλη";
        $langOfStudents = "Μελών";
        $langCStudent = "ΜΕΛΟΣ";
        $langCStudent2 = "ΜΕΛΟΣ";

        $langsStudent = "μέλος";
        $langsOfStudent = "μέλους";
        $langsStudents = "μέλη";
        $langsOfStudents = "μελών";
        $langsOfStudentss = "μέλη";
        $langsstudent_acc = "μέλος";

        $langCourse = "Συνεργασία";
        $langCourses = "Συνεργασίες";
        $langOfCourses = "Συνεργασιών";
        $langOfCourse = "Συνεργασίας";

        $langsCourse = "συνεργασία";
        $langsCourses = "συνεργασίες";
        $langsOfCourse = "συνεργασίας";
        $langsOfCourses = "συνεργασιών";
        $langCourseS = "συνεργασίας";
        $langMyCourses = "Οι συνεργασίες μου";

        $langInfoAbout = "Η πλατφόρμα $siteName αποτελεί ένα ολοκληρωμένο Σύστημα Διαχείρισης Ηλεκτρονικών Συνεργασιών. Ακολουθεί τη φιλοσοφία του λογισμικού ανοικτού κώδικα και υποστηρίζει την υπηρεσία Ασύγχρονης Τηλεκπαίδευσης χωρίς περιορισμούς και δεσμεύσεις. Η πρόσβαση στην υπηρεσία γίνεται με τη χρήση ενός απλού φυλλομετρητή (web browser) χωρίς την απαίτηση εξειδικευμένων τεχνικών γνώσεων.";

    }elseif(!get_config('show_always_collaboration') and get_config('show_collaboration')){
       
    }
}