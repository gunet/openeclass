<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 * @subpackage navigation
 *
 * This script creates the top frame needed when we browse a module that needs to use frame
 * This appens when the module is SCORM (@link http://www.adlnet.org )
 * or made by the user with his own html pages.
 */

/*======================================
       CLAROLINE MAIN
  ======================================*/
  $require_current_course = TRUE;
  $langFiles              = "learnPath";
  require("../../../include/init.php");

  $navigation[]= array ("url"=>"../learningPathList.php", "name"=> $langLearningPathList);
  if ( $is_adminOfCourse )
  {
       $navigation[]= array ("url"=>"../learningPathAdmin.php", "name"=> $langLearningPathAdmin);
  }
  else
  {
       $navigation[]= array ("url"=>"../learningPath.php", "name"=> $langLearningPath);
  }
  $navigation[]= array ("url"=>"../module.php", "name"=> $langModule);
  //$htmlHeadXtra[] = "<script src=\"APIAdapter.js\" type=\"text/javascript\" language=\"JavaScript\">";
  begin_page();


?>
