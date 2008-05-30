<?php

/**=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
                     Yannis Exidaridis <jexi@noc.uoa.gr> 
                     Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	scromAPI.inc.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: scormAPI.inc.php Revision: 1.12.2.2
	      
	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Lederer Guillaume <led@cerdecam.be>
==============================================================================        
    @Description: This file must be included when the module browsed is SCORM 
                  conformant. This script supplies the SCORM API 
                  implementation in javascript for browsers like NS and 
                  Mozilla. This script is the client side API javascript 
                  generated for user with browser like NS and Mozilla.

    @Comments:
 
    @todo: 
==============================================================================
*/

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$TABLEUSERS             = "user";


if($uid)
{
    mysql_select_db($mysqlMainDb);
    // Get user first and last name
    $sql = "SELECT nom, prenom
              FROM `".$TABLEUSERS."` AS U
             WHERE U.`user_id` = ". (int)$uid;
    $userDetails = db_query_get_single_row($sql);
    
    mysql_select_db($currentCourseID);
    // Get general information to generate the right API inmplementation
    $sql = "SELECT *
              FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP,
                   `".$TABLELEARNPATHMODULE."` AS LPM,
                   `".$TABLEMODULE."` AS M
             WHERE UMP.`user_id` = ". (int)$uid."
               AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
               AND M.`module_id` = LPM.`module_id`
               AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
               AND LPM.`module_id` = ". (int)$_SESSION['lp_module_id'];
    $userProgressionDetails = db_query_get_single_row($sql);
    $userProgressionDetails['nom'] = $userDetails['nom'];
    $userProgressionDetails['prenom'] = $userDetails['prenom'];
}

if( !$uid || !$userProgressionDetails )
{
    $sco['student_id'] = "-1";
    $sco['student_name'] = "Anonymous, User";
    $sco['lesson_location'] = "";
    $sco['credit'] ="no-credit";
    $sco['lesson_status'] = "not attempted";
    $sco['entry'] = "ab-initio";
    $sco['raw'] = "";
    $sco['scoreMin'] = "";
    $sco['scoreMax'] = "";
    $sco['total_time'] = "0000:00:00.00";
    $sco['suspend_data'] = "";
    $sco['launch_data'] = "";
}
else // authenticated user and no error in query
{
    // set vars
    $sco['student_id'] = $uid;
    $sco['student_name'] = $userProgressionDetails['nom'].", ".$userProgressionDetails['prenom'];
    $sco['lesson_location'] = $userProgressionDetails['lesson_location'];
    $sco['credit'] = strtolower($userProgressionDetails['credit']);
    $sco['lesson_status'] = strtolower($userProgressionDetails['lesson_status']);
    $sco['entry'] = strtolower($userProgressionDetails['entry']);
    $sco['raw'] = ($userProgressionDetails['raw'] == -1) ? "" : "".$userProgressionDetails['raw'];
    $sco['scoreMin'] = ($userProgressionDetails['scoreMin'] == -1) ? "" : "".$userProgressionDetails['scoreMin'];
    $sco['scoreMax'] = ($userProgressionDetails['scoreMax'] == -1) ? "" : "".$userProgressionDetails['scoreMax'];
    $sco['total_time'] = $userProgressionDetails['total_time'];
    $sco['suspend_data'] = $userProgressionDetails['suspend_data'];
    $sco['launch_data'] = stripslashes($userProgressionDetails['launch_data']);
}


//common vars
$sco['_children'] = "student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,exit,session_time";
$sco['score_children'] = "raw,min,max";
$sco['exit'] = "";
$sco['session_time'] = "0000:00:00.00";

?>

<script type="text/javascript">

        var init_total_time = "<?php echo $sco['total_time']; ?>";
        // ====================================================
        // API Class Constructor
        var debug_ = false;
        function APIClass() {

                //SCORM 1.2

                // Execution State
                this.LMSInitialize = LMSInitialize;
                this.LMSFinish = LMSFinish;

                // Data Transfer
                this.LMSGetValue = LMSGetValue;
                this.LMSSetValue = LMSSetValue;
                this.LMSCommit = LMSCommit;

                // State Management
                this.LMSGetLastError = LMSGetLastError;
                this.LMSGetErrorString = LMSGetErrorString;
                this.LMSGetDiagnostic = LMSGetDiagnostic;

                // Private
                this.APIError = APIError;

                // SCORM 2004

                // Execution State
                this.Initialize = LMSInitialize;
                this.Terminate = LMSFinish;

                // Data Transfer
                this.GetValue = LMSGetValue;
                this.SetValue = LMSSetValue;
                this.Commit = LMSCommit;

                // State Management
                this.GetLastError = LMSGetLastError;
                this.GetErrorString = LMSGetErrorString;
                this.GetDiagnostic = LMSGetDiagnostic;

        }


        // ====================================================
        // Execution State
        //

        // Initialize
        // According to SCORM 1.2 reference :
        //    - arg must be "" (empty string)
        //    - return value : "true" or "false"
        function LMSInitialize(arg) {
                if(debug_) alert("initialize");
                if ( arg!="" ) {
                        this.APIError("201");
                        return "false";
                }
                this.APIError("0");
                APIInitialized = true;

                return "true";
        }
        // Finish
        // According to SCORM 1.2 reference
        //    - arg must be "" (empty string)
        //    - return value : "true" or "false"
        function LMSFinish(arg) {
                if(debug_) alert("LMSfinish");
                if ( APIInitialized ) {
                        if ( arg!="" ) {
                                this.APIError("201");
                                return "false";
                        }
                        this.APIError("0");
                        
                        setTimeout("do_commit()",1000);
                      
                        APIInitialized = false; //
                        return "true";
                } else {
                        this.APIError("403");   // not initialized
                        return "false";
                }
        }


        // ====================================================
        // Data Transfer
        //
        function LMSGetValue(ele) {
                if(debug_) alert("LMSGetValue : \n" + ele);
                if ( APIInitialized )
                {
                       var i = array_indexOf(elements,ele);
                       if (i != -1 )  // ele is implemented -> handle it
                       {
                           switch (ele)
                           {
                                case 'cmi.core._children' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.student_id' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.student_name' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.lesson_location' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.credit' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.lesson_status' :
                                      APIError("0");
                                      return values[i];
                                      break;

                                //-----------------------------------
                                //deal with SCORM 2004 new elements :
                                //-----------------------------------

                                case 'cmi.completion_status' :
                                      APIError("0");
                                      ele = 'cmi.core.lesson_status';
                                      return values[i];
                                      break;

                                case 'cmi.success_status' :
                                      APIError("0");
                                      ele = 'cmi.core.lesson_status';
                                      return values[i];
                                      break;

                                //-----------------------------------

                                case 'cmi.core.entry' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.score._children' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.score.raw' :
                                case 'cmi.score.raw' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.score.min' :
                                case 'cmi.score.min' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.score.max' :
                                case 'cmi.score.max' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.total_time' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.core.exit' :
                                      APIError("405"); // write only
                                      return "";
                                      break;
                                case 'cmi.core.session_time' :
                                case 'cmi.session_time' :
                                      APIError("405"); // write only
                                      return "";
                                      break;

                                case 'cmi.suspend_data' :
                                      APIError("0");
                                      return values[i];
                                      break;
                                case 'cmi.launch_data' :
                                      APIError("0");
                                      return values[i];
                                      break;

                           }
                       }
                       else // ele not implemented
                       {
                            // not implemented error
                            APIError("401");
                            return "";
                       }
                }
                else
                {
                        // not initialized error
                        this.APIError("403");
                        return "false";
                }
        }

        function LMSSetValue(ele,val) {
                if(debug_) alert ("LMSSetValue : \n" + ele +" "+ val);
                if ( APIInitialized )
                {
                       var i = array_indexOf(elements,ele);
                       if (i != -1 )  // ele is implemented -> handle it
                       {
                           switch (ele)
                           {
                                case 'cmi.core._children' :
                                      APIError("404"); // read only
                                      return "false";
                                      break;
                                case 'cmi.core.student_id' :
                                      APIError("404"); // read only
                                      return "false";
                                      break;
                                case 'cmi.core.student_name' :
                                      APIError("404"); // read only
                                      return "false";
                                      break;
                                case 'cmi.core.lesson_location' :
                                      if( val.length > 255 )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.core.lesson_status' :
                                      var upperCaseVal = val.toUpperCase();
                                      if ( upperCaseVal != "PASSED" && upperCaseVal != "FAILED"
                                           && upperCaseVal != "COMPLETED" && upperCaseVal != "INCOMPLETE"
                                           && upperCaseVal != "BROWSED" && upperCaseVal != "NOT ATTEMPTED" )
                                      {
                                           APIError("406");
                                           return "false";
                                      }

                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;


                                //-------------------------------
                                // Deal with SCORM 2004 element :
                                // completion_status and success_status are new element,
                                // we use them together with the old element lesson_status in the claro DB
                                //-------------------------------

                                case 'cmi.completion_status' :
                                      var upperCaseVal = val.toUpperCase();
                                      if ( upperCaseVal != "PASSED" && upperCaseVal != "FAILED"
                                           && upperCaseVal != "COMPLETED" && upperCaseVal != "INCOMPLETE"
                                           && upperCaseVal != "BROWSED" && upperCaseVal != "NOT ATTEMPTED" && upperCaseVal != "UNKNOWN" )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      ele = 'cmi.core.lesson_status';
                                      values[4] = val;  // deal with lesson_status element from scorm 1.2 instead
                                      APIError("0");
                                      return "true";
                                      break;

                                case 'cmi.success_status' :
                                      var upperCaseVal = val.toUpperCase();
                                      if ( upperCaseVal != "PASSED" && upperCaseVal != "FAILED"
                                           && upperCaseVal != "COMPLETED" && upperCaseVal != "INCOMPLETE"
                                           && upperCaseVal != "BROWSED" && upperCaseVal != "NOT ATTEMPTED" && upperCaseVal != "UNKNOWN" )
                                      {
                                           APIError("406");
                                           return "false";
                                      }

                                      ele = 'cmi.core.lesson_status';
                                      values[4] = val;  // deal with lesson_status element from scorm 1.2 instead
                                      APIError("0");
                                      return "true";
                                      break;

                                //-------------------------------


                                case 'cmi.core.credit' :
                                      APIError("404"); // read only
                                      return "false";
                                      break;
                                case 'cmi.core.entry' :
                                      APIError("404"); // read only
                                      return "false";
                                      break;
                                case 'cmi.core.score._children' :
                                      APIError("404");  // read only
                                      return "false";
                                      break;
                                case 'cmi.core.score.raw' :
                                      if( isNaN(parseInt(val)) || (val < 0) || (val > 100) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.score.raw' :
                                      if( isNaN(parseInt(val)) || (val < 0) || (val > 100) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[8] = val; // SCORM 2004, we deal with the old element
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.core.score.min' :
                                      if( isNaN(parseInt(val)) || (val < 0) || (val > 100) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.score.min' :
                                      if( isNaN(parseInt(val)) || (val < 0) || (val > 100) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[14] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.core.score.max' :
                                      if( isNaN(parseInt(val)) || (val < 0) || (val > 100) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.score.max' :
                                      if( isNaN(parseInt(val)) || (val < 0) || (val > 100) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[15] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.core.total_time' :
                                      APIError("404"); //read only
                                      return "false";
                                      break;
                                case 'cmi.core.exit' :
                                      var upperCaseVal = val.toUpperCase();
                                      if ( upperCaseVal != "TIME-OUT" && upperCaseVal != "SUSPEND"
                                           && upperCaseVal != "LOGOUT" && upperCaseVal != "" )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.core.session_time' :
                                      // regexp to check format
                                      // hhhh:mm:ss.ss or PThHmMsS
                                      var re = /^[0-9]{2,4}:[0-9]{2}:[0-9]{2}(.)?[0-9]?[0-9]?$/;
                                      var re2 = /^PT[0-9]{1,2}H[0-9]{1,2}M[0-9]{2}(.)?[0-9]?[0-9]?S$/;

                                      if ( !re.test(val) && !re2.test(val) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }

									  // check that minuts and second are 0 <= x < 60
									  if (re.test(val)) // only for SCORM 1.2
									  {
										var splitted_val = val.split(":");
										if( splitted_val[1] < 0 || splitted_val[1] >= 60 || splitted_val[2] < 0 || splitted_val[2] >= 60 )
										{
											APIError("406");
											return "false";
										}
									  }
									  
                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.session_time' :
                                      // regexp to check format
                                      // hhhh:mm:ss.ss or PThHmMsS
                                      var re = /^[0-9]{2,4}:[0-9]{2}:[0-9]{2}(.)?[0-9]?[0-9]?$/;
                                      var re2 = /^PT[0-9]{1,2}H[0-9]{1,2}M[0-9]{2}(.)?[0-9]?[0-9]?S$/;

                                      if ( !re.test(val) && !re2.test(val) )
                                      {
                                           APIError("406");
                                           return "false";
                                      }

									  // check that minuts and second are 0 <= x < 60
									  if (re.test(val)) // only for SCORM 1.2
									  {
										var splitted_val = val.split(":");
										if( splitted_val[1] < 0 || splitted_val[1] >= 60 || splitted_val[2] < 0 || splitted_val[2] >= 60 )
										{
											APIError("406");
											return "false";
										}
									  }
									  
                                      values[11] = val; // SCORM 2004, use together with the old element
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.suspend_data' :
                                      if( val.length > 4096 )
                                      {
                                           APIError("406");
                                           return "false";
                                      }
                                      values[i] = val;
                                      APIError("0");
                                      return "true";
                                      break;
                                case 'cmi.launch_data' :
                                      APIError("404"); //read only
                                      return "false";
                                      break;

                           }
                       }
                       else // ele not implemented
                       {
                            // not implemented error
                            APIError("401");
                            return "";
                       }
                }
                else
                {
                        // not initialized error
                        this.APIError("403");
                        return "false";
                }
        }

        function LMSCommit(arg)
        {
               if(debug_) alert("LMScommit");
               if ( APIInitialized ) {
                        if ( arg!="" ) {
                                this.APIError("201");
                                return "false";
                        } else {
                                this.APIError("0");
                                
                                do_commit();

                                return "true";
                        }
                } else {
                        this.APIError("403");
                        return "false";
                }
        }


        // ====================================================
        // State Management
        //
        function LMSGetLastError() {
                if(debug_) alert ("LMSGetLastError : " + APILastError);
                
                return APILastError;               
        }

        function LMSGetErrorString(num) {
                if(debug_) alert ("LMSGetErrorString(" + num +") = " + errCodes[num] );
                
                return errCodes[num];

        }

        function LMSGetDiagnostic(num) {
                if(debug_) alert ("LMSGetDiagnostic("+num+") = " + errDiagn[num] );
                
                if ( num=="" ) num = APILastError;
                return errDiagn[num];
        }


        // ====================================================
        // Private
        //
        function APIError(num) {
                APILastError = num;
        }

        // ====================================================
        // Error codes and Error diagnostics
        //
        var errCodes = new Array();
        errCodes["0"]   = "No Error";
        errCodes["101"] = "General Exception";
        errCodes["102"] = "General Initialization Failure";
        errCodes["103"] = "Already Initialized";
        errCodes["104"] = "Content Instance Terminated";
        errCodes["111"] = "General Termination Failure";
        errCodes["112"] = "Termination Before Initialization";
        errCodes["113"] = "Termination After Termination";
        errCodes["122"] = "Retrieve Data Before Initialization";
        errCodes["123"] = "Retrieve Data After Termination";
        errCodes["132"] = "Store Data Before Initialization";
        errCodes["133"] = "Store Data After Termination";
        errCodes["142"] = "Commit Before Initialization";
        errCodes["143"] = "Commit After Termination";
        errCodes["201"] = "General Argument Error";
        errCodes["301"] = "General Get Failure";
        errCodes["351"] = "General Set Failure";
        errCodes["391"] = "General Commit Failure";
        errCodes["401"] = "Undefined Data Model Element";
        errCodes["402"] = "Unimplemented Data Model Element";
        errCodes["403"] = "Data Model Element Value Not Initialized";
        errCodes["404"] = "Data Model Element Is Read Only";
        errCodes["405"] = "Data Model Element Is Write Only";
        errCodes["406"] = "Data Model Element Type Mismatch";
        errCodes["407"] = "Data Model Element Value Out Of Range";

        var errDiagn = new Array();
        errDiagn["0"]   = "No Error";
        errDiagn["101"] = "General Exception";
        errDiagn["102"] = "General Initialization Failure";
        errDiagn["103"] = "Already Initialized";
        errDiagn["104"] = "Content Instance Terminated";
        errDiagn["111"] = "General Termination Failure";
        errDiagn["112"] = "Termination Before Initialization";
        errDiagn["113"] = "Termination After Termination";
        errDiagn["122"] = "Retrieve Data Before Initialization";
        errDiagn["123"] = "Retrieve Data After Termination";
        errDiagn["132"] = "Store Data Before Initialization";
        errDiagn["133"] = "Store Data After Termination";
        errDiagn["142"] = "Commit Before Initialization";
        errDiagn["143"] = "Commit After Termination";
        errDiagn["201"] = "General Argument Error";
        errDiagn["301"] = "General Get Failure";
        errDiagn["351"] = "General Set Failure";
        errDiagn["391"] = "General Commit Failure";
        errDiagn["401"] = "Undefined Data Model Element";
        errDiagn["402"] = "Unimplemented Data Model Element";
        errDiagn["403"] = "Data Model Element Value Not Initialized";
        errDiagn["404"] = "Data Model Element Is Read Only";
        errDiagn["405"] = "Data Model Element Is Write Only";
        errDiagn["406"] = "Data Model Element Type Mismatch";
        errDiagn["407"] = "Data Model Element Value Out Of Range";
        



        // ====================================================
        // CMI Elements and Values
        //
        var elements = new Array();
        elements[0]  = "cmi.core._children";
        elements[1]  = "cmi.core.student_id";
        elements[2]  = "cmi.core.student_name";
        elements[3]  = "cmi.core.lesson_location";
        elements[4]  = "cmi.core.lesson_status";
        elements[5]  = "cmi.core.credit";
        elements[6]  = "cmi.core.entry";
        elements[7]  = "cmi.core.score._children";
        elements[8]  = "cmi.core.score.raw";
        elements[9]  = "cmi.core.total_time";
        elements[10] = "cmi.core.exit";
        elements[11] = "cmi.core.session_time";
        elements[12] = "cmi.suspend_data";
        elements[13] = "cmi.launch_data";
        elements[14] = "cmi.core.score.min";
        elements[15] = "cmi.core.score.max";
        elements[16] = "cmi.completion_status";
        elements[17] = "cmi.success_status";
        elements[18] = "cmi.session_time";
        elements[19] = "cmi.score.raw";
        elements[20] = "cmi.score.min";
        elements[21] = "cmi.score.max";

        var values = new Array();
        values[0]  = "<?php echo $sco['_children']; ?>";
        values[1]  = "<?php echo $sco['student_id']; ?>";
        values[2]  = "<?php echo $sco['student_name']; ?>";
        values[3]  = "<?php echo $sco['lesson_location']; ?>";
        values[4]  = "<?php echo $sco['lesson_status']; ?>";
        values[5]  = "<?php echo $sco['credit']; ?>";
        values[6]  = "<?php echo $sco['entry']; ?>";
        values[7]  = "<?php echo $sco['score_children']; ?>";
        values[8]  = "<?php echo $sco['raw']; ?>";
        values[9]  = "<?php echo $sco['total_time'] ?>";
        values[10] = "<?php echo $sco['exit']; ?>";
        values[11] = "<?php echo $sco['session_time']; ?>";
        values[12] = "<?php echo $sco['suspend_data']; ?>";
        values[13] = "<?php echo $sco['launch_data']; ?>";
        values[14] = "<?php echo $sco['scoreMin']; ?>";
        values[15] = "<?php echo $sco['scoreMax']; ?>";
        values[16] = "<?php echo $sco['lesson_status'] ?>"; //we do deal the completion_status element with the old lesson_status element, this will change in further versions...
        values[17] = "<?php echo $sco['lesson_status'] ?>"; //we do deal the sucess_status element with the old lesson_status element, this will change in further versions...
        values[18] = "<?php echo $sco['session_time']; ?>"; // we do deal the session_time element with the old element
        values[19] = "<?php echo $sco['raw']; ?>"; // we do deal the score.raw element with the old element
        values[20] = "<?php echo $sco['scoreMin']; ?>"; // we do deal the score.min element with the old element
        values[21] = "<?php echo $sco['scoreMax']; ?>"; // we do deal the score.max element with the old element


        // ====================================================
        // 
        //
        function do_commit()
        {
              // target form is in a hidden frame
              cmiform = upFrame.document.forms[0];
              // user module progress id
              cmiform.ump_id.value = "<?php echo $userProgressionDetails['user_module_progress_id'] ?>";
              // values to set in DB
              cmiform.lesson_location.value = values[3];
              cmiform.lesson_status.value = values[4];
              cmiform.credit.value = values[5];
              cmiform.entry.value = values[6];              
              cmiform.raw.value = values[8];
              cmiform.total_time.value = values[9];
              cmiform.session_time.value = values[11];
              cmiform.suspend_data.value = values[12];
              cmiform.scoreMin.value = values[14];
              cmiform.scoreMax.value = values[15];
              cmiform.submit();
        }

        function array_indexOf(arr,val) {
                for ( var i=0; i<arr.length; i++ ) {
                        if ( arr[i] == val ) {
                                return i;
                        }
                }
                return -1;
        }


        // ====================================================
        // Final Setup
        //


        APIInitialized = false;
        APILastError = "403";

        // Declare Scorm API object for 1.2

        API = new APIClass();
        api = new APIClass();

        // Declare Scorm API object for 2004

        API_1484_11 = new APIClass();
        api_1484_11 = new APIClass();



</script>
