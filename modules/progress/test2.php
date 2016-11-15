<?php 

require_once '../../include/baseTheme.php';
require_once 'vendor/autoload.php';

$mpdf = new mPDF('utf-8', 'A4-L', 0, '', 0, 0, 0, 0, 0, 0);

$html_certificate = file_get_contents($urlServer.'modules/progress/certificate_float_mm.html');

$certificate_title = "Πιστοποιητικό παρακολούθησης";
$student_name = $_SESSION['givenname']." ".$_SESSION['surname'];

$html_certificate = preg_replace('(%certificate_title%)', $certificate_title, $html_certificate);
$html_certificate = preg_replace('(%student_name%)', $student_name, $html_certificate);

$mpdf->WriteHTML($html_certificate);

$mpdf->Output();