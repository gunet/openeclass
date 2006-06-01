<?php
/*
 * Created on 1 Éïõí 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once '../../include/libchart/libchart.php';
$query = "SELECT * FROM actions WHERE 1";
$result = db_query($query, $currentCourseID);

$chart = new PieChart(500, 300);
$chart->addPoint(new Point("Bleu d'Auvergne", 50));
$chart->addPoint(new Point("Tomme de Savoie", 75));
$chart->addPoint(new Point("Crottin de Chavignol", 30));

$chart->setTitle("Preferred Cheese");
$path = '../../courses/'.$currentCourseID.'/image/usagegraph.png';
$chart->render($path);

$tool_content .=
'
<img src="'.$urlServer.'/courses/'.$currentCourseID.'/image/usagegraph.png" />
';

?>
