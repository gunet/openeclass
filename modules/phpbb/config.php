<?php  
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$topics_per_page = 10;
$posts_per_page = 10;
$hot_threshold = 30;

$url_images = "images";
$url_admin = "../forum_admin";

//$folder_image = "../../template/classic/img/folder.gif";
$folder_image = "$url_images/topic_read.gif";
$icon_topic_latest = "$url_images/icon_topic_latest.gif";
$hot_folder_image = $newposts_image = $folder_image;
$hot_newposts_image = "$url_images/topic_read_hot.gif";
$posticon = "$url_images/posticon.png";
$posticon_more = "$url_images/icon_pages.gif";
$locked_image = "$url_images/lock.png";
?>
