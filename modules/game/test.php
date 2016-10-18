<?php 

require_once '../../include/baseTheme.php';

// Include the main TCPDF library
require_once __DIR__.'/../../include/tcpdf/tcpdf_include.php';
require_once __DIR__.'/../../include/tcpdf/tcpdf.php';

$user = 'Αθανάσιος Πλέσσας';
$course = 'Προγραμματισμός σε PHP';
$grade = 'Άριστα';
$date = '01/06/2016';

$html_certificate = 
'<div style="height:100%; padding:20px; text-align:center; border: 5px solid #787878">
       <span style="font-size:50px; font-weight:bold">Certificate of Completion</span>
       <br><br>
       <span style="font-size:25px"><i>This is to certify that</i></span>
       <br><br>
       <span style="font-size:30px"><b>'.$user.'</b></span><br/><br/>
       <span style="font-size:25px"><i>has completed the course</i></span> <br/><br/>
       <span style="font-size:30px">'.$course.'</span> <br/><br/>
       <span style="font-size:20px">with score of <b>'.$grade.'</b></span> <br/><br/><br/><br/>
       <span style="font-size:25px"><i>dated</i></span><br>
      '.$date.'
      <span style="font-size:30px">&nbsp;<br/><br/><br/></span>
</div>';


// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle('Certificate');
$pdf->SetSubject('Certificate');
// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, '');

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();

$pdf->writeHTML($html_certificate, true, false, true, false, '');

$pdf->Ln();

$pdf->Output('certificate.pdf', 'D');