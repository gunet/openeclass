<?

// $Id$ 
/**
 * this segment   read  properties  of a course.
 *
 *  before 
 *			$code_cours was DB name or dir name 
 *			$fake_code = code of course, when code_cours is not anymore the code cours desired by  prof
 *  now
 *			$currentCourseCode content real code  of  course,  only  used to be show  or  
 *  				to search the course"
 *			$currentCourseID  content the unique id for  system base and dir = this id unique
 *
 * note ( to Be confirmed )
 *			to have admin facilities whe search,  $currentCourseID is buils like this
 *			$currentCourseID = $currentCourseCode . $theCourse["cours_id"];
 *			$currentCourseID must be stored ( and not calculated by concat every time) because
 *			$currentCourseCode can change
 *
 * 
 */ 

mysql_select_db("$mysqlMainDb",$db);


// just make sure that the $uid variable isn't faked
if (isset($_SESSION['uid']))
        $uid = $_SESSION['uid'];
else
        unset($uid);

if (!isset($_SESSION['dbname']))
	exit("session is lost, please go back to course homepage and refresh");
else
	$dbname = $_SESSION['dbname'];

if (!isset($_SESSION["is_admin"])) {
	$is_admin = FALSE;
} else {
	$is_admin = $_SESSION["is_admin"];
}

$currentCourse = $dbname; //$dbname 

// we try to meet here all condition we can give access to admin of a course
// When all script use $is_adminOfCourse, that's became  more easy 
// to implement a multi-level acces of admin

// actually a  prof have $status 1 or 2 
if ( isset ($status)&&($status[$currentCourse]==1 OR $status[$currentCourse]==2))
	$is_adminOfCourse = TRUE;
else
	$is_adminOfCourse = FALSE;

// $default_language is  just a best name  for  language fixed in config.
$default_language = $language;
$result = mysql_query("
SELECT 
		code, fake_code, 
		intitule, faculte, 
		titulaires, languageCourse, 
		departmentUrlName, departmentUrl
	FROM cours
	WHERE cours.code='$currentCourse'" );
while ($theCourse = mysql_fetch_array($result)) 
{
	$fake_code 	= $theCourse["fake_code"];
	$code_cours = $theCourse["code"];
	$intitule 	= $theCourse["intitule"];
	$fac 		= $theCourse["faculte"];
	$titulaires	= $theCourse["titulaires"];
	$languageInterface = $theCourse["languageCourse"];
	$departmentUrl= $theCourse["departmentUrl"];
	$departmentUrlName= $theCourse["departmentUrlName"];
// new var names

// most important change
//  before 
//			$code_cours was DB name or dir name 
//			$fake_code = code of course, when code_cours is not anymore the code cours desired by  prof
//  now
//			$currentCourseCode content real code  of  course,  only  used to be show  or  
//  				to search the course"
//			$currentCourseID  content the unique id for  system base and dir = this id unique

// note ( to Be confirmed )
//			to have admin facilities whe search,  $currentCourseID is buils like this
//			$currentCourseID = $currentCourseCode . $theCourse["cours_id"];
//			$currentCourseID must be stored ( and not calculated by concat every time) because
//			$currentCourseCode can change

	$currentCourseCode				= $fake_code ;
	$currentCourseID				= $code_cours;
	$currentCourseName				= $intitule;
	$currentCourseDepartment		= $fac;
	$currentCourseTitular 			= $titulaires;
	$currentCourseLanguage			= $languageInterface;
	$currentCourseDepartmentUrl		= $departmentUrl;
	$currentCourseDepartmentUrlName	= $departmentUrlName; 

}
if (!isset($code_cours)||$code_cours=="")
	exit("This course doesn't exist");
$fac_lower = strtolower($fac);
$language = $languageInterface ;



?>
