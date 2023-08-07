<?php

// Message file for language es
// Generated 2016-08-11 13:21:11

$langCourse = "Curso";
$langEclass = "eClass Libre - Sistema de Administración del Curso";
$langYes = "Si";
$langNo = "No";
$langTeacher = "Formador";
$langOfTeacher = "Formador";
$langTeachers = "Formadores";
$langOfTeachers = "Formadores";
$langsTeacher = "Formador";
$langsOfTeacher = "formador";
$langsTeachers = "formadores";
$langsOfTeachers = "formadores";
$langStudent = "Aprendiz";
$langOfStudent = "Aprendiz";
$langStudents = "Aprendices";
$langOfStudents = "Aprendices";
$langsStudent = "aprendiz";
$langsOfStudent = "aprendiz";
$langsStudents = "aprendices";
$langsOfStudents = "aprendices";
$langsOfStudentss = "Estudiantes";
$langsstudent_acc = $langsStudent;
$langGuest = "Usuario invitado";
$langGuests = "Usuarios invitados";
$langFaculty = "Categoría del curso";
$langOfFaculty = "Category";
$langOfFaculties = "Categories";
$langFaculties = "Categories";
$langsFaculty = "facultad";
$langsFaculties = "facultades";
$langpres = "Undergraduate";
$langposts = "Postgraduate";
$langothers = "Otros";
$langpre = "Undergraduate";
$langpost = "Postgrado";
$langother = "Otro";
$langInfoAbout = "La <strong>$siteName</strong> plaforma is un sistema completo de Administración de Curson que soporta Servicios de eLearning Asincrónicos con un simple navegador Web. Su meta es la incorporación y el uso constructivo de Internet y las tecnologías de la Web en los procesos de enseañanza y aprendizaje. Soporta administración electrónica, almacenamiento y presentación de materiales para la enseñanza, independientes de los factores constrictores como el espacio y el tiempo y creando las condiciones necesarias para un entorno de enseñanza dinámico.<br><br><br><br> La introducción de e-learning en un proceso de aprendizaje tradicional proporciona nuevas capacidades y permite nuevas fuentes de interacción entre aprendices y formadores, $langsOfTeacher and $langsOfStudent, a través de entornos technologicos contemporáneos.";
$langExtrasLeft = '';
$langExtrasRight = '';
$langCourses = "curso";
$langGroupTutor = "Group Tutor";
$langEditor = "Tutor Assistant";
$langOfEditor = "Tutor Assistant";
$langsEditor = "tutor assistant";
$langsOfEditor = "tutor assistant";
$langsOfGroupTutor = "group tutor";
$langOfCourse = "Course";
$langOfCourses = "Courses";
$langsCourse = "course";
$langsCourses = "courses";
$langsOfCourse = "course";
$langsOfCourses = "courses";
$langsOfFaculty = "category";




if (file_exists('config/config.php')) {
    if(get_config('mentoring_always_active')){
        $langTeacher = "Coordinator";
        $langOfTeacher = "Coordinator";
        $langTeachers = "Coordinators";
        $langOfTeachers = "Coordinators";

        $langsTeacher = "coordinator";
        $langsOfTeacher = "coordinator";
        $langsTeachers = "coordinators";
        $langsOfTeachers = "coordinators";
        $langCTeacher = "COORDINATOR";

        $langStudent = "Mentee";
        $langOfStudent = "Mentee";
        $langStudents = "Mentees";
        $langOfStudents = "Mentees";
        $langCStudent = "MENTEE";

        $langsStudent = "mentee";
        $langsOfStudent = "mentee";
        $langsStudents = "mentees";
        $langsOfStudents = "mentees";
        $langsOfStudentss = "mentees";
        $langsstudent_acc = "mentee";

        $langCourse = "Program";
        $langCourses = "Programs";
        $langOfCourses = "Programs";
        $langOfCourse = "Program";

        $langsCourse = "program";
        $langsCourses = "programs";
        $langsOfCourse = "program";
        $langsOfCourses = "programs";
    }
}