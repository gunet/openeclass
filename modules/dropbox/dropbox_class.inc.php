<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * - Dropbox_Person:
 * 		. userId
 * 		. receivedWork 	=> array of Dropbox_Work objects
 * 		. sentWork 		=> array of Dropbox_SentWork objects 
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
		    $this->createNewWork($arg1, $arg2, $arg3, $arg4, $arg5, $arg6);
		} else {
			$this->createExistingWork($arg1);
		}
	}

        /*
        * private function creating a new work object
        */
	private function createNewWork ($uploaderId, $title, $description, $author, $filename, $filesize) {
		
		global $course_id;
		/*
		* Do some sanity checks
		*/
		settype($uploaderId, 'integer');
		/*
		* Fill in the properties
		*/
		$this->uploaderId = $uploaderId;
		$this->uploaderName = uid_to_name($this->uploaderId);
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
		if ($GLOBALS['language'] == 'el') {
			$sql="SELECT id, DATE_FORMAT(uploadDate, '%d-%m-%Y / %H:%i')
				FROM dropbox_file
				WHERE course_id = $course_id AND filename = '".addslashes($this->filename)."'";
		} else {
			$sql="SELECT id, DATE_FORMAT(uploadDate, '%Y-%m-d% / %H:%i')
				FROM dropbox_file
				WHERE course_id = $course_id AND filename = '".addslashes($this->filename)."'";
		}
		$result = db_query($sql);
		$res = mysql_fetch_array($result);
		if ($res != FALSE) $this->isOldWork = TRUE;

		/*
		* insert or update the dropbox_file table and set the id property
		*/
		if ($this->isOldWork) {
			$this->id = $res["id"];
			$this->uploadDate = $res["uploadDate"];
		    $sql = "UPDATE dropbox_file
					SET filesize = '".addslashes($this->filesize)."'
					, title = '".addslashes($this->title)."'
					, description = '".addslashes($this->description)."'
					, author = '".addslashes($this->author)."'
					, lastUploadDate = '".addslashes($this->lastUploadDate)."'
					WHERE id='".addslashes($this->id)."'";
			$result = db_query($sql);
		} else {
			$this->uploadDate = $this->lastUploadDate;
			$sql="INSERT INTO dropbox_file(course_id, uploaderId, filename, filesize, title, description, author, uploadDate, lastUploadDate)
				VALUES ('".addslashes($course_id)."'
						, '".addslashes($this->uploaderId)."'
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
		$sql="INSERT INTO dropbox_person(fileId, personId)
			VALUES ($this->id, $this->uploaderId)";
                
                $result = db_query($sql);	//if work already exists no error is generated
	}

        /*
        * private function creating existing object by retreiving info from db
	*/
        private function createExistingWork($id) {
		
		global $langAnonymous;

		/*
		* Do some sanity checks
		*/
		settype($id, 'integer');
		/*
		* get the data from DB
		*/
                if ($GLOBALS['language'] == 'el') {
                        $sql="SELECT uploaderId, filename, filesize, title, description, author,
                                DATE_FORMAT(uploadDate, '%d-%m-%Y / %H:%i') AS uploadDate,
                                DATE_FORMAT(lastUploadDate, '%d-%m-%Y / %H:%i') AS lastUploadDate
                                FROM dropbox_file
                                WHERE id='".addslashes($id)."'";
                } else {
                        $sql="SELECT uploaderId, filename, filesize, title, description, author,
                                DATE_FORMAT(uploadDate, '%Y-%m-%d / %H:%i') AS uploadDate,
                                DATE_FORMAT(lastUploadDate, '%Y-%m-%d / %H:%i') AS lastUploadDate
                                FROM dropbox_file
                                WHERE id='".addslashes($id)."'";
                }
	        $result = db_query($sql);
		$res = mysql_fetch_array($result);		
		$uploaderId = stripslashes($res["uploaderId"]);
		$uploaderName = uid_to_name($uploaderId);
		if ($uploaderName == FALSE) {
			//deleted user
			$this->uploaderId = -1;
			$this->uploaderName = $langAnonymous;
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
	/*
        * Constructor calls private functions to create a new work or retreive an existing work from DB
        * depending on the number of parameters
        */
	public function Dropbox_SentWork ($arg1, $arg2=null, $arg3=null, $arg4=null, $arg5=null, $arg6=null, $arg7=null) {
		if (func_num_args() > 1) {
                        $this->createNewSentWork($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7);
		} else {
			$this->createExistingSentWork($arg1);
		}
	}
        /*
        * private function creating a new SentWork object        
        */		
	private function createNewSentWork($uploaderId, $title, $description, $author, $filename, $filesize, $recipientIds) {
		

		/*
		* Call constructor of Dropbox_Work object
		*/
		$this->Dropbox_Work($uploaderId, $title, $description, $author, $filename, $filesize);
		/*
		* Do sanity checks on recipientIds array & property fillin
		* The sanity check for ex-coursemembers is already done in base constructor
		*/
		settype($uploaderId, 'integer');

		$justSubmit = FALSE;  // RH: mailing zip-file or just upload
		if (is_int($recipientIds))
		{
			$justSubmit = TRUE; 
                        $recipientIds = array($recipientIds + $this->id);
		}
		elseif (count($recipientIds) == 0)  // RH: Just Upload
		{
			$justSubmit = TRUE; 
                        $recipientIds = array($uploaderId);
		}		
		foreach ($recipientIds as $rec) {			
			$this->recipients[] = array("id"=>$rec, "name"=>uid_to_name($rec));
		}
		/*
		* insert data in dropbox_post and dropbox_person table for each recipient
		*/
		foreach ($this->recipients as $rec) {
			$sql="INSERT INTO dropbox_post(fileId, recipientId)
				VALUES ('".addslashes($this->id)."', '".addslashes($rec["id"])."')";
                        $result = db_query($sql);	//if work already exists no error is generated
			//insert entries into person table
			$sql="INSERT INTO dropbox_person(fileId, personId)
				VALUES ($this->id, $rec[id])";
			if (!$justSubmit) {
                                $result = db_query($sql);
                        }
		}
	}

        /*
        * private function creating existing object by retreiving info from db
        */
        
        private function createExistingSentWork($id) {

                global $langAnonymous;

                /*
                * Call constructor of Dropbox_Work object
                */
                $this->Dropbox_Work($id);

                /*
                * Do sanity check
                * The sanity check for ex-coursemembers is already done in base constructor
                */
                settype($id, 'integer');

                /*
                * Fill in recipients array
                */
                $this->recipients = array();
                $sql="SELECT recipientId FROM dropbox_post WHERE fileId = $id";
                $result = db_query($sql);
                while ($res = mysql_fetch_array($result)) {
                        /*
                        * check for deleted users
                        */
                        $recipientId = $res["recipientId"];
                        $recipientName = uid_to_name($recipientId);
                        if ($recipientName == FALSE) {
                                $this->recipients[] = array("id"=> -1, "name"=> $langAnonymous);
                        } else {
                                $this->recipients[] = array("id"=>$recipientId, "name"=>$recipientName);
                        }
                }
	}
}

class Dropbox_Person {
	var $receivedWork;	//array of Dropbox_Work objects
	var $sentWork;		//array of Dropbox_SentWork objects
        var $allsentWork;        
	var $userId = 0;
	var $isCourseAdmin = FALSE;
	var $_orderBy = '';	//private property that determines by which field
						//the receivedWork and the sentWork arrays are sorted

	public function Dropbox_Person ($userId, $isCourseAdmin) {
		/*
		* Constructor for recreating the Dropbox_Person object
		*/
		global $course_id;
		/*
		* Fill in properties
		*/
		$this->userId = intval($userId);
		$this->isCourseAdmin = $isCourseAdmin;		
		$this->receivedWork = array();
		$this->sentWork = array();
                $this->allsentWork = array();                
		/*
		* find all entries where this person is the recipient
		*/
		$sql = "SELECT r.fileId FROM dropbox_post r, dropbox_person p, dropbox_file f
				WHERE r.recipientId = $this->userId
					AND r.recipientId = p.personId
					AND r.fileId = p.fileId
					AND r.fileId = f.id
					AND f.course_id = $course_id";
                
        	$result = db_query($sql);
		while ($res = mysql_fetch_array($result)) {
			$this->receivedWork[] = new Dropbox_Work($res["fileId"]);
		}
		/*
		* find all entries where this person is the sender/uploader
		*/
		$sql = "SELECT f.id FROM dropbox_file f, dropbox_person p
				WHERE f.course_id = $course_id
				AND f.uploaderId = $this->userId
				AND f.uploaderId = p.personId
				AND f.id = p.fileId";
                $result = db_query($sql);
                while ($res = mysql_fetch_array($result)) {
                        $this->sentWork[] = new Dropbox_SentWork($res["id"]);
                }
                        
                /*
		* find all uploader entries
		*/		                
                $sql = "SELECT DISTINCT f.id FROM dropbox_file f, dropbox_person p, dropbox_post r
				WHERE f.course_id = $course_id
				AND f.uploaderId = p.personId
                                AND f.uploaderId != $this->userId
				AND f.id = p.fileId
                                AND r.recipientId != $this->userId
                                AND r.fileId = f.id";
                $result = db_query($sql);
                while ($res = mysql_fetch_array($result)) {
                        $this->allsentWork[] = new Dropbox_SentWork($res["id"]);
                }                
        }
			       
        /*
        * Deletes all the received work of this person
        */	
	public function deleteAllReceivedWork () {
     
		//delete entries in person table concerning received works
		foreach ($this->receivedWork as $w) {
			db_query("DELETE FROM dropbox_person
				WHERE personId='".$this->userId."' AND fileId='".$w->id."'");
		}
		removeUnusedFiles();	//check for unused files
	}
        
        /*
        * Deletes a received work of this person with id=$id
        */
	public function deleteReceivedWork($id) {
						
		foreach($this->receivedWork as $w) {
			if ($w->id == $id) {
                           break;
			}
		}
		//delete entries in person table concerning received works
		db_query("DELETE FROM dropbox_person WHERE personId = $this->userId AND fileId = $id");
		removeUnusedFiles();	//check for unused files
	}
        
        /*
        * Deletes all the sent work of this person
        */		
	public function deleteAllSentWork() {
		
		foreach ($this->sentWork as $w) {
			db_query("DELETE FROM dropbox_person
				  WHERE personId=$this->userId 
                                        AND fileId=$w->id");
		}
		removeUnusedFiles();	//check for unused files
	}

        /*
        * Deletes a sent work of this person with id=$id
        */
	public function deleteSentWork($id) {
					
		foreach($this->sentWork as $w) {
			if ($w->id == $id) {
			   break;
			}
		}		

		//delete entries in person table concerning sent works
		db_query("DELETE FROM dropbox_person
				WHERE personId = $this->userId AND fileId = $id");
		removeUnusedFiles();	//check for unused files
	}
        
        // delete file from users dropbox
        public function deleteWork($id) {
                
                global $dropbox_cnf;
                
                db_query("DELETE FROM dropbox_post WHERE fileId = $id");
                db_query("DELETE FROM dropbox_person WHERE fileId = $id");
                
                $filename = db_query_get_single_value("SELECT filename FROM dropbox_file WHERE id = $id");
                db_query("DELETE FROM dropbox_file WHERE id = $id");                
                
                //delete file
                unlink($dropbox_cnf["sysPath"] . "/" . $filename);
        }
}