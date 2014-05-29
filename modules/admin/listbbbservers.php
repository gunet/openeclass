<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'bbb-api.php';

function get_connected_users($salt,$bbb_url)
{
    // Instatiate the BBB class:
    $bbb = new BigBlueButton($salt,$bbb_url);

    $meetings = $bbb->getMeetingsWithXmlResponseArray();

    $sum = 0;
    foreach($meetings as $meeting){
            $mid = $meeting['meetingId'];
            $pass = $meeting['moderatorPw'];
            if($mid != null){
                    $info = $bbb->getMeetingInfoWithXmlResponseArray(array('meetingId' => $mid, 'password' => $pass));
                    $sum += $info['participantCount'];
            }
    }
    return $sum;

}

/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$aColumns = array( 'id','hostname', 'ip', 'enabled','server_key','api_url','max_users' );
	
/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "hostname";
	
/* 
 * Paging
 */
$sLimit = "";
if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
{
	$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
	intval( $_GET['iDisplayLength'] );
}


/*
 * Ordering
 */
$sOrder = "";
if ( isset( $_GET['iSortCol_0'] ) )
{
	$sOrder = "ORDER BY  ";
	for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
	{
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
		{
			$sOrder .= "`".$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."` ".
			($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
		}
	}
		
	$sOrder = substr_replace( $sOrder, "", -2 );
	if ( $sOrder == "ORDER BY" )
	{
		$sOrder = "";
	}
}

/* 
 * Filtering
 * NOTE this does not match the built-in DataTables filtering which does it
 * word by word on any field. It's possible to do here, but concerned about efficiency
 * on very large tables, and MySQL's regex functionality is very limited
 */
$sWhere = "";
if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
{
	$sWhere = "WHERE (";
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
		{
			$sWhere .= "`".$aColumns[$i]."` LIKE '%".quote( $_GET['sSearch'] )."%' OR ";
		}
	}
	$sWhere = substr_replace( $sWhere, "", -3 );
	$sWhere .= ')';
}

/* Individual column filtering */
for ( $i=0 ; $i<count($aColumns) ; $i++ )
{
	if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
	{
		if ( $sWhere == "" )
		{
			$sWhere = "WHERE ";
		}
		else
		{
			$sWhere .= " AND ";
		}
		$sWhere .= "`".$aColumns[$i]."` LIKE '%".quote($_GET['sSearch_'.$i])."%' ";
	}
}


/*
 * SQL queries
 * Get data to display
 */

$rResult=db_query("SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."`
	FROM   bbb_servers
	$sWhere
	$sOrder
	$sLimit");

/* Data set length after filtering */
$sQuery = "
	SELECT FOUND_ROWS()
";

$rResultFilterTotal = db_query($sQuery);
$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

/* Total data set length */
$sQuery = "
	SELECT COUNT(`".$sIndexColumn."`)
	FROM   bbb_servers
";

$rResultTotal = db_query($sQuery);
$aResultTotal = mysql_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];


/*
 * Output
 */
$output = array(
	"sEcho" => intval($_GET['sEcho']),
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);

while ( $aRow = mysql_fetch_array( $rResult ) )
{
	$row = array();
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
            if ( $aColumns[$i] == "version" )
            {
                /* Special output formatting for 'version' column */
		$row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
		}
            else if ( $aColumns[$i] != ' ' )
            {
                /* General output */
		$row[] = $aRow[ $aColumns[$i] ];
            }
	}
        $connected_users = get_connected_users($row[4],$row[5]) . "/" . $row[6];

        //Remove elements from array to complete json data
        unset($row[4]);
        unset($row[5]);
        unset($row[6]);
        
        array_push($row,"<a href='bbbmoduleconf.php?edit_server=".$row[0]."'>Edit server</a>");
        array_push($row, "$connected_users");
        array_push($row,"<a href='bbbmoduleconf.php?delete_server=".$row[0]."' onClick='return confirmation(\"$langConfirmDelete\");'>Remove server</a>");
        array_shift($row);
        $output['aaData'][] = $row;
        
}

echo json_encode( $output );
?>