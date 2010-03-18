<?php 
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/**
 * Classes for the dropbox module.
 * 
 * 3 classes are defined:
 * - Dropbox_Work:
 * 		. id
 * 		. uploaderId	=> who sent it		// RH: Mailing: or mailing pseudo_id
 * 		. uploaderName
 * 		. filename		=> name of file stored on the server
 * 		. filesize							// RH: Mailing: zero for sent zip
 * 		. title			=> name of file returned to user. This is the original name of the file
 * 		except when the original name contained spaces. In that case the spaces
 * 		will be replaced by _
 * 		. description
 * 		. author
 * 		. uploaddate	=> date when file was first sent
 * 		. lastUploadDate=> date when file was last sent
 *  	. isOldWork 	=> has the work already been uploaded before
 * 
 * - Dropbox_SentWork extends Dropbox_Work
 * 		. recipients	=> array of ["id"]["name"] lists the recipients of the work
 * 		// RH: Mailing: or mailing pseudo_id
 * - Dropbox_Person:
 * 		. userId
 * 		. receivedWork 	=> array of Dropbox_Work objects
 * 		. sentWork 		=> array of Dropbox_SentWork objects
 * 		. isCourseTutor
 * 		. isCourseAdmin
 * 		. _orderBy		=>private property used for determining the field by which the works have 
 * to be ordered
 *
 **/

class Dropbox_Work {
	var $id;
	var $uploaderId;
	var $uploaderName;
	var $filename;
	var $filesize;
	var $title;
	var $description;
	var $author;
	var $uploadDate;
	var $lastUploadDate;
	var $isOldWork;
	
	function Dropbox_Work ($arg1, $arg2=null, $arg3=null, $arg4=null, $arg5=null, $arg6=null) {
		/*
		* Constructor calls private functions to create a new work or retreive an existing work from DB
		* depending on the number of parameters
		*/
		if (func_num_args()>1) {
		    $this->_createNewWork($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
		} else {
			$this->_createExistingWork($arg1);
		}
	}
	
	function _createNewWork ($uploaderId, $title, $description, $author, $filename, $filesize) {
		/*
		* private function creating a new work object
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;
		
		/*
		* Do some sanity checks
		*/
		settype($uploaderId, 'integer') or die($dropbox_lang["generalError"]); //set $uploaderId to correct type
		
		/*
		* Fill in the properties
		*/
		$this->uploaderId = $uploaderId; 
		$this->uploaderName = getUserNameFromId($this->uploaderId);
		$this->filename = $filename;
		$this->filesize = $filesize;
		$this->title = $title;
		$this->description = $description;
		$this->author = $author;
		$this->lastUploadDate = date("Y-m-d H:i",time());

		/*
		* Check if object exists already. If it does, the old object is used 
		* with updated information (authors, descriptio, uploadDate)
		*/
		$this->isOldWork = FALSE;
		if ($GLOBALS['language'] == 'greek') {
			$sql="SELECT id, DATE_FORMAT(uploadDate, '%d-%m-%Y / %H:%i')
				FROM `".$dropbox_cnf["fileTbl"]."` 
				WHERE filename = '".addslashes($this->filename)."'";
		} else {
			$sql="SELECT id, DATE_FORMAT(uploadDate, '%Y-%m-d% / %H:%i')
				FROM `".$dropbox_cnf["fileTbl"]."` 
				WHERE filename = '".addslashes($this->filename)."'";
		}
        	$result = db_query($sql,$currentCourseID);
		$res = mysql_fetch_array($result);
		if ($res != FALSE) $this->isOldWork = TRUE;
		
		/*
		* insert or update the dropbox_file table and set the id property
		*/
		if ($this->isOldWork) {
			$this->id = $res["id"];
			$this->uploadDate = $res["uploadDate"];
		    $sql = "UPDATE `".$dropbox_cnf["fileTbl"]."`
					SET filesize = '".addslashes($this->filesize)."'
					, title = '".addslashes($this->title)."'
					, description = '".addslashes($this->description)."'
					, author = '".addslashes($this->author)."'
					, lastUploadDate = '".addslashes($this->lastUploadDate)."'
					WHERE id='".addslashes($this->id)."'";
			$result = db_query($sql);
		} else {
			$this->uploadDate = $this->lastUploadDate;
			$sql="INSERT INTO `".$dropbox_cnf["fileTbl"]."` 
				(uploaderId, filename, filesize, title, description, author, uploadDate, lastUploadDate)
				VALUES ('".addslashes($this->uploaderId)."'
						, '".addslashes($this->filename)."'
						, '".addslashes($this->filesize)."'
						, '".addslashes($this->title)."'
						, '".addslashes($this->description)."'
						, '".addslashes($this->author)."'
						, '".addslashes($this->uploadDate)."'
						, '".addslashes($this->lastUploadDate)."'
						)";

        	$result = db_query($sql);		
			$this->id = mysql_insert_id(); //get automatically inserted id
		}
		
		
		/*
		* insert entries into person table
		*/
		$sql="INSERT INTO `".$dropbox_cnf["personTbl"]."` 
				(fileId, personId)
				VALUES ('".addslashes($this->id)."'
						, '".addslashes($this->uploaderId)."'
						)";
        $result = db_query($sql);	//if work already exists no error is generated
	}
	
	function _createExistingWork ($id) {
		/*
		* private function creating existing object by retreiving info from db
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;
		
		/*
		* Do some sanity checks
		*/
		settype($id, 'integer') or die($dropbox_lang["generalError"]); //set $id to correct type

		/*
		* get the data from DB
		*/
	if ($GLOBALS['language'] == 'greek') {
		$sql="SELECT uploaderId, filename, filesize, title, description, author,
			DATE_FORMAT(uploadDate, '%d-%m-%Y / %H:%i') AS uploadDate, 
			DATE_FORMAT(lastUploadDate, '%d-%m-%Y / %H:%i') AS lastUploadDate
			FROM `".$dropbox_cnf["fileTbl"]."`
			WHERE id='".addslashes($id)."'";
	} else {
		$sql="SELECT uploaderId, filename, filesize, title, description, author,
			DATE_FORMAT(uploadDate, '%Y-%m-%d / %H:%i') AS uploadDate, 
			DATE_FORMAT(lastUploadDate, '%Y-%m-%d / %H:%i') AS lastUploadDate
			FROM `".$dropbox_cnf["fileTbl"]."`
			WHERE id='".addslashes($id)."'";
	}
	        $result = db_query($sql, $currentCourseID);
		$res = mysql_fetch_array($result);;
		
		/*
		* Check if uploader is still in claroline system
		*/
		$uploaderId = stripslashes($res["uploaderId"]);    
		$uploaderName = getUserNameFromId($uploaderId);
		if ($uploaderName == FALSE) {
			//deleted user
			$this->uploaderId = -1;
			$this->uploaderName = $dropbox_lang["anonymous"];			
		} else {
			$this->uploaderId = $uploaderId; 
			$this->uploaderName = $uploaderName;			
		}
		
		/*
		* Fill in properties
		*/
		$this->id = $id;
		$this->filename = stripslashes($res["filename"]);
		$this->filesize = stripslashes($res["filesize"]);
		$this->title = stripslashes($res["title"]);
		$this->description = stripslashes($res["description"]);
		$this->author = stripslashes($res["author"]);
		$this->uploadDate = stripslashes($res["uploadDate"]);
		$this->lastUploadDate = stripslashes($res["lastUploadDate"]);
		
	}
}

class Dropbox_SentWork extends Dropbox_Work {
	var $recipients;	//array of ["id"]["name"] arrays
	
	function Dropbox_SentWork ($arg1, $arg2=null, $arg3=null, $arg4=null, $arg5=null, $arg6=null, $arg7=null) {
		/*
		* Constructor calls private functions to create a new work or retreive an existing work from DB
		* depending on the number of parameters
		*/
		if (func_num_args()>1) {
		    $this->_createNewSentWork ($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
		} else {
			$this->_createExistingSentWork ($arg1);
		}
	}

	function _createNewSentWork ($uploaderId, $title, $description, $author, $filename, $filesize, $recipientIds) {
		/*
		* private function creating a new SentWork object
		*
		* RH: Mailing: $recipientIds is integer instead of array (mailing zip)
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;

		/*
		* Call constructor of Dropbox_Work object
		*/
		$this->Dropbox_Work($uploaderId, $title, $description, $author, $filename, $filesize);

		/*
		* Do sanity checks on recipientIds array & property fillin
		* The sanity check for ex-coursemembers is already done in base constructor
		*/
		settype($uploaderId, 'integer') or die($dropbox_lang["generalError"]); //set $uploaderId to correct type
		
		$justSubmit = FALSE;  // RH: mailing zip-file or just upload
		if (is_int($recipientIds))
		{
			$justSubmit = TRUE; $recipientIds = array($recipientIds + $this->id);
		}
		elseif ( count($recipientIds) == 0)  // RH: Just Upload
		{
			$justSubmit = TRUE; $recipientIds = array($uploaderId);
		}
		if (! is_array($recipientIds) || count($recipientIds) == 0) die($dropbox_lang["generalError"]);
		foreach ($recipientIds as $rec) {
			if (empty($rec)) die($dropbox_lang["generalError"]);
			//if (!isCourseMember($rec)) die(); //cannot sent document to someone outside of course
				//this check is done when validating submitted data
			$this->recipients[] = array("id"=>$rec, "name"=>getUserNameFromId($rec));
		}
		
		/*
		* insert data in dropbox_post and dropbox_person table for each recipient
		*/
		foreach ($this->recipients as $rec) {	
			$sql="INSERT INTO `".$dropbox_cnf["postTbl"]."` 
				(fileId, recipientId)
				VALUES ('".addslashes($this->id)."', '".addslashes($rec["id"])."')";
	        $result = db_query($sql,$currentCourseID);	//if work already exists no error is generated
						
			//insert entries into person table
			$sql="INSERT INTO `".$dropbox_cnf["personTbl"]."` (fileId, personId)
				VALUES ('".addslashes($this->id)."', '".addslashes($rec["id"])."')";
        	// RH: do not add recipient in person table if mailing zip or just upload
			if (!$justSubmit) $result = db_query($sql);	//if work already exists no error is generated
		}
	}
	
	function _createExistingSentWork  ($id) {
		/*
		* private function creating existing object by retreiving info from db
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;

		/*
		* Call constructor of Dropbox_Work object
		*/
		$this->Dropbox_Work($id);
		
		/*
		* Do sanity check
		* The sanity check for ex-coursemembers is already done in base constructor
		*/
		settype($id, 'integer') or die($dropbox_lang["generalError"]); //set $id to correct type

		/*
		* Fill in recipients array
		*/
		$this->recipients = array();
		$sql="SELECT recipientId
				FROM `".$dropbox_cnf["postTbl"]."`
				WHERE fileId='".addslashes($id)."'";
	        $result = db_query($sql,$currentCourseID);
		while ($res = mysql_fetch_array($result)) {
			/*
			* check for deleted users
			*/
			$recipientId = $res["recipientId"];
			$recipientName = getUserNameFromId($recipientId);
			if ($recipientName == FALSE) {
				$this->recipients[] = array("id"=>-1, "name"=> $dropbox_lang["anonymous"]);
			} else {
				$this->recipients[] = array("id"=>$recipientId, "name"=>$recipientName);
			}
		}
	}
}

class Dropbox_Person {
	var $receivedWork;	//array of Dropbox_Work objects
	var $sentWork;		//array of Dropbox_SentWork objects
	var $userId = 0;
	var $isCourseAdmin = FALSE;
	var $isCourseTutor = FALSE;
	var $_orderBy = '';	//private property that determines by which field 
						//the receivedWork and the sentWork arrays are sorted

	function Dropbox_Person ($userId, $isCourseAdmin, $isCourseTutor) {
		/*
		* Constructor for recreating the Dropbox_Person object
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;
		
		/*
		* Fill in properties
		*/
		$this->userId = $userId;
		$this->isCourseAdmin = $isCourseAdmin;
		$this->isCourseTutor = $isCourseTutor;	
		$this->receivedWork = array();
		$this->sentWork = array();

		//Note: perhaps include an ex coursemember check to delete old files
		
		/*
		* find all entries where this person is the recipient 
		*/
		$sql = "SELECT r.fileId FROM 
				`".$dropbox_cnf["postTbl"]."` r
				, `".$dropbox_cnf["personTbl"]."` p
				WHERE r.recipientId = '".addslashes($this->userId)."' 
					AND r.recipientId = p.personId
					AND r.fileId = p.fileId";
        	$result = db_query($sql, $currentCourseID);
		while ($res = mysql_fetch_array($result)) {
			$this->receivedWork[] = new Dropbox_Work($res["fileId"]);
		}
		
		/*
		* find all entries where this person is the sender/uploader
		*/
		$sql = "SELECT f.id FROM `".$dropbox_cnf["fileTbl"]."` f, `".$dropbox_cnf["personTbl"]."` p 
				WHERE f.uploaderId = '".addslashes($this->userId)."'
				AND f.uploaderId = p.personId
				AND f.id = p.fileId";
        $result = db_query($sql, $currentCourseID);
		while ($res = mysql_fetch_array($result)) {
			$this->sentWork[] = new Dropbox_SentWork($res["id"]);
		}
	}
	
	
	function _cmpWork ($a, $b) {
		/*
		* This private method is used by the usort function in  the 
		* orderSentWork and orderReceivedWork methods. 
		* It compares 2 work-objects by 1 of the properties of that object, dictated by the 
		* private property _orderBy.
		* It returns -1, 0 or 1 dependent of the result of the comparison.
		*/
		$sort = $this->_orderBy;
		$aval = $a->$sort;
		$bval = $b->$sort;
		if ($sort == 'recipients') {	//the recipients property is an array so we do the comparison based 
					//on the first item of the recipients array
		    $aval = $aval[0]['name'];
			$bval = $bval[0]['name'];
		}
		if ($sort == 'filesize') {	//filesize is not a string, so we use other comparison technique
			return $aval<$bval ? -1 : 1;
		} else {
		    return strcasecmp($aval, $bval);
		}
	}
	
	
	function orderSentWork($sort) {
		/*
		* method that sorts the objects in the sentWork array, dependent on the $sort parameter.
		* $sort can be lastDate, firstDate, title, size, ...
		*/
		switch($sort){
			case 'lastDate': 
				$this->_orderBy = 'lastUploadDate';
				break;
			case 'firstDate': 
				$this->_orderBy = 'uploadDate';
				break;
			case 'title': 
				$this->_orderBy = 'title';
				break;
			case 'size': 
				$this->_orderBy = 'filesize';
				break;
			case 'author': 
				$this->_orderBy = 'author';
				break;
			case 'recipient': 
				$this->_orderBy = 'recipients';
				break;
			default:
				$this->_orderBy = 'lastUploadDate';
		} // switch
		
		usort($this->sentWork, array($this,"_cmpWork"));	//this calls the _cmpWork method	
	}
	
	function orderReceivedWork($sort) {
		/*
		* method that sorts the objects in the receivedWork array, dependent on the $sort parameter.
		* $sort can be lastDate, firstDate, title, size, ...
		*/
		switch($sort){
			case 'lastDate': 
				$this->_orderBy = 'lastUploadDate';
				break;
			case 'firstDate': 
				$this->_orderBy = 'uploadDate';
				break;
			case 'title': 
				$this->_orderBy = 'title';
				break;
			case 'size': 
				$this->_orderBy = 'filesize';
				break;
			case 'author': 
				$this->_orderBy = 'author';
				break;
			case 'sender': 
				$this->_orderBy = 'uploaderName';
				break;
			default:
				$this->_orderBy = 'lastUploadDate';
		} // switch
		
		usort($this->receivedWork, array($this,"_cmpWork"));	//this calls the _cmpWork method		
	}
	
	function deleteAllReceivedWork () {
		/*
		* Deletes all the received work of this person
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;
	
		//delete entries in person table concerning received works
		foreach ($this->receivedWork as $w) {
			db_query("DELETE FROM `".$dropbox_cnf["personTbl"]."` 
				WHERE personId='".$this->userId."' AND fileId='".$w->id."'", $currentCourseID);
		}
		removeUnusedFiles();	//check for unused files

	}
	
	function deleteReceivedWork ($id) {
		/*
		* Deletes a received work of this person with id=$id
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;

		//id check
		$found = false;
		foreach($this->receivedWork as $w) {
			if ($w->id == $id) {
			   $found = true; break;
			}
		}
		if (! $found) die($dropbox_lang["generalError"]);
		
		//delete entries in person table concerning received works
		db_query("DELETE FROM `".$dropbox_cnf["personTbl"]."` 
			WHERE personId='".$this->userId."' AND fileId='".$id."'", $currentCourseID);
		
		removeUnusedFiles();	//check for unused files
	}
	
	function deleteAllSentWork () {
		/*
		* Deletes all the sent work of this person
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;
	
		//delete entries in person table concerning sent works
		foreach ($this->sentWork as $w) {
			db_query("DELETE FROM `".$dropbox_cnf["personTbl"]."` 
				WHERE personId='".$this->userId."' AND fileId='".$w->id."'", $currentCourseID);
			removeMoreIfMailing($w->id);  // RH: Mailing: see init1
		}		
		removeUnusedFiles();	//check for unused files

	}
	
	function deleteSentWork ($id) {
		/*
		* Deletes a sent work of this person with id=$id
		*/
		global $dropbox_cnf, $dropbox_lang, $currentCourseID;

		//index check
		$found = false;
		foreach($this->sentWork as $w) {
			if ($w->id == $id) {
			   $found = true; break;
			}
		}
		if (!$found) die($dropbox_lang["generalError"]);
		
		//delete entries in person table concerning sent works
		db_query("DELETE FROM `".$dropbox_cnf["personTbl"]."` 
				WHERE personId='".$this->userId."' AND fileId='".$id."'", $currentCourseID);
		
		removeMoreIfMailing($id);  // RH: Mailing: see init1
		removeUnusedFiles();	//check for unused files
	}
}

?>
