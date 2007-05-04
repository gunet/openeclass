<?
/*
      +----------------------------------------------------------------------+
      | GUnet eClass 2.0                                                     |
      | Asychronous Teleteaching Platform                                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2003-2007  GUnet                                       |
      +----------------------------------------------------------------------+
      |                                                                      |
      | GUnet eClass 2.0 is an open platform distributed in the hope that    |
      | it will be useful (without any warranty), under the terms of the     |
      | GNU License (General Public License) as published by the Free        |
      | Software Foundation. The full license can be read in "license.txt".  |
      |                                                                      |
      | Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       |
      |                        Yannis Exidaridis <jexi@noc.uoa.gr>           |
      |                        Alexandros Diamantidis <adia@noc.uoa.gr>      |
      |                        Tilemachos Raptis <traptis@noc.uoa.gr>        |
      |                                                                      |
      | For a full list of contributors, see "credits.txt".                  |
      |                                                                      |
      +----------------------------------------------------------------------+
      | Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  |
      |                  Network Operations Center, University of Athens,    |
      |                  Panepistimiopolis Ilissia, 15784, Athens, Greece    |
      +----------------------------------------------------------------------+
*/


/*
Header
*/

$langAddComment = "Add a comment";
$langAddIntro = "Add introduction text";
$langAddModule = "Add";
$langAddModulesButton = "Add selection";
$langAddOneModuleButton = "Add module";
$langAgenda = "Agenda";
$langAlertBlockingMakedInvisible = "This module is blocked. Making it invisible will allow students to access the next module without having to complete this one. Confirm ?";
$langAlertBlockingPathMadeInvisible = "This path is blocking. Making it invisible will allow students to access the next path without having to complete this one. Confirm ?";
$langAlreadyBrowsed = "Already browsed";
$langAltClarodoc = "Clarodoc";
$langAltDocument = "Document";
$langAltExercise = "Exercise";
$langAltMakeNotBlocking = "Unblock";
$langAltMakeVisible = "Make visible";
$langAltMove = "Move";
$langAltMoveUp = "Move up";
$langAltScorm = "Scorm";
$langAnnouncement = "Announcements";
$langAreYouSureDeleteModule = "Are you sure to totally delete this module ? It will be completely deleted from the server and from any learning path it is in. You won't be able to use it in any learning path. Confirm delete of : ";
$langAreYouSureToDelete = "Are you sure to delete";
$langAreYouSureToDeleteScorm = "This learning path is part of a SCORM importation package. If you delete this path, all its SCORM conformant modules and related files will be deleted from the platform. Are you sure you want to delete the learning path named ";
$langAreYouSureToRemove = "Are you sure you want to remove the following module from the learning path: ";
$langAreYouSureToRemoveLabel = "By deleting a label you will delete all modules or label it contains.";
$langAreYouSureToRemoveSCORM = "SCORM conformant modules are definitively removed from server when deleted in their learning path.";
$langAreYouSureToRemoveStd = "The module will still be available in the pool of modules.";
$langBack = "Back";
$langBackModule = "Back to list";
$langBackToLPAdmin = "Back to learning path administration";
$langBlock = "Block";
$langBrowserCannotSeeFrames = "Your browser cannot see frames.";
$langCancel = "Cancel";
$langChangeRaw = "Change minimum raw mark to pass this module (percentage): ";
$langChat = "Chat";
$langComment = "Comment";
$langConfirmYourChoice = "Please confirm your choice";
$langCopy = "Copy";
$langCourseDescription = "Course Description";
$langCourseDescriptionAsModule = "Use Course Description";
$langCourseHome = "Course Home";
$langCourseManager = "Course manager";
$langCreateLabel = "Create label";
$langCreateNewLearningPath = "Create a new learning path";
$langDOCUMENTTypeDesc = "Document";
$langDate = "Date";
$langDay_of_weekNames = "Array";
$langDefaultLearningPathComment = "This is the introduction text of this learning path. To replace it by your own text, click below on <b>modify</b>.";
$langDefaultModuleAddedComment = "This an additional introduction text about the presence of this module specially into this learning path. To replace it by your own text, click below on <b>modify</b>.";
$langDefaultModuleComment = "This is the introduction text of this module, it will appears in each learning path that contains this module. To replace it by your own text, click below on <b>modify</b>.";
$langDelete = "Delete";
$langDescriptionCours = "Course description";
$langDocInsertedAsModule = "has been added as module";
$langDocument = "Documents and Links";
$langDocumentAlreadyUsed = "This document is already used as a module in this learning path";
$langDocumentAsModule = "Use a document";
$langDocumentInModule = "Document in module";
$langEXERCISETypeDesc = "Eclass exercise";
$langEmail = "Email";
$langEndOfSteps = "Click exit after completing this last step.";
$langErrorAssetNotFound = "Asset not found: ";
$langErrorCopyAttachedFile = "Unable to copy file: ";
$langErrorCopyScormFiles = "Error when copying needed SCORM files";
$langErrorCopyingScorm = "Error copying existing SCORM content";
$langErrorCreatingDirectory = "Unable to create directory: ";
$langErrorCreatingFile = "Unable to create file: ";
$langErrorCreatingFrame = "Unable to create frame file";
$langErrorCreatingManifest = "Unable to create the SCORM manifest (imsmanifest.xml)";
$langErrorCreatingScormArchive = "Unable to create the SCORM archive";
$langErrorEmptyName = "Name must be completed";
$langErrorFileMustBeZip = "File must be a zip file (.zip)";
$langErrorInvalidParms = "Error: Invalid parameter (use numbers only)";
$langErrorLoadingExercise = "Unable to load the exercise";
$langErrorLoadingQuestion = "Unable to load exercise's question";
$langErrorNameAlreadyExists = "Error: Name already exists in the learning path or in the module pool";
$langErrorNoModuleInPackage = "No module in package";
$langErrorNoZlibExtension = "Zlib php extension is required to use this tool.  Please contact your platform administrator.";
$langErrorOpeningManifest = "Cannot find <i>manifest</i> file in the package.<br /> File not found : imsmanifest.xml";
$langErrorOpeningXMLFile = "Cannot find secondary initialisation file in the package.<br /> File not found : ";
$langErrorReadingManifest = "Error reading <i>manifest</i> file";
$langErrorReadingXMLFile = "Error reading a secondary initialisation file : ";
$langErrorReadingZipFile = "Error reading zip file.";
$langErrorSql = "Error in SQL statement";
$langErrorValuesInDouble = "Error: One or more values are doubled";
$langErrortExtractingManifest = "Cannot extract manifest from zip file (corrupted file ? ).";
$langExAlreadyUsed = "This exercise is already used as a module in this learning path";
$langExInsertedAsModule = "has been added as a module of the course and of this learning path";
$langExercise = "Exercise";
$langExerciseAsModule = "Use an exercise";
$langExerciseCancelled = "Exercise cancelled, choose the next module to continue by clicking next.";
$langExerciseDone = "Exercise done, choose the next module to continue by clicking next.";
$langExerciseInModule = "Exercise in module";
$langExercises = "Exercises";
$langExport = "Export";
$langExport2004 = "Export in SCORM 2004 format";
$langExport12 = "Export in SCORM 1.2 format";
$langFailed = "Failed";
$langFileError = "The file to upload is not valid.";
$langFileName = "Filename";
$langFirstName = "First Name";
$langForbidden = "Not allowed";
$langForums = "Forums";
$langFullScreen = "Fullscreen";
$langGlobalProgress = "Learning path progression : ";
$langGroups = "Groups";
$langHelp = "Help";
$langImport = "Import";
$langInFrames = "In frames";
$langInfoProgNameTitle = "Information";
$langInsertMyDescToolName = "Insert course description";
$langInsertMyDocToolName = "Insert a document as module";
$langInsertMyExerciseToolName = "Insert my exercise";
$langInsertMyLinkToolName = "Insert a link as module";
$langInsertMyModuleToolName = "Insert my module";
$langInsertMyModulesTitle = "Insert a module of the course";
$langInsertNewModuleName = "Insert new name";
$langInstalled = "Learning path has been successfully imported.";
$langIntroLearningPath = "Use this tool to provide your students with a sequential path between documents, exercises, HTML pages, links,...<br /><br />If you want to present your learning path to students, click on the button below.<br />";
$langLINKTypeDesc = "Link";
$langLastName = "Last Name";
$langLastSessionTimeSpent = "Last session time";
$langLearningPath = "Learning Path";
$langLearningPathAdmin = "Learning Path Admin";
$langLearningPathEmpty = "Learning Path is empty";
$langLearningPathList = "Learning Paths List";
$langLearningPathName = "New learning path name: ";
$langLearningPathNotFound = "Learning Path not found";
$langLessonStatus = "Module status";
$langLinkAlreadyUsed = "This link is already used as a module in this learning path";
$langLinkAsModule = "Use a Link";
$langLinkInsertedAsModule = "has been added as a module of the course and of this learning path";
$langLogin = "Login";
$langLogout = "Logout";
$langMakeInvisible = "Make invisible";
$langMaxFileSize = "Max file size: ";
$langMinuteShort = "min.";
$langModify = "Modify";
$langModifyProfile = "My User Account";
$langModule = "Module";
$langModuleMoved = "Module moved";
$langModuleOfMyCourse = "Use a module of this course";
$langModuleStillInPool = "Modules of this path will still be available in the pool of modules";
$langModules = "Modules";
$langModulesPoolToolName = "Pool of modules";
$langMonthNames = "Array";
$langMove = "Move";
$langMoveDown = "Move down";
$langMyAgenda = "My calendar";
$langMyCourses = "My course list";
$langName = "Name";
$langNameOfLang = "Array";
$langNeverBrowsed = "Never browsed";
$langNewLabel = "Create a new label / title in this learning path";
$langNext = "Next";
$langNextPage = "Next Page";
$langNoEmail = "No email address specified";
$langNoEx = "There is no exercise for the moment";
$langNoLearningPath = "No learning path";
$langNoModule = "No module";
$langNoMoreModuleToAdd = "All modules of this course are already used in this learning path.";
$langNoSpace = "The upload has failed. There is not enough space in your directory";
$langNoStartAsset = "There is no start asset defined for this module.";
$langNotAllowed = "Not allowed";
$langNotAttempted = "Not attempted";
$langNotInstalled = "An error occured.  Learning Path import failed.";
$langNotice = "Notice";
$langOk = "Ok";
$langOkChapterHeadAdded = "Title added: ";
$langOkDefaultCommentUsed = "Warning: Installation cannot find the description of the learning path and has set a default comment.  You should change it";
$langOkDefaultTitleUsed = "Warning: Installation cannot find the name of the learning path and has set a default name.  You should change it.";
$langOkFileReceived = "File received: ";
$langOkManifestFound = "Manifest found in zip file: ";
$langOkManifestRead = "Manifest read.";
$langOkModuleAdded = "Module added: ";
$langOrder = "Order";
$langOtherCourses = "Course list";
$langPassed = "Passed";
$langPathContentTitle = "Learning path content";
$langPathsInCourseProg = "Course progression ";
$langPeriodDayShort = "d.";
$langPeriodHourShort = "h.";
$langPersoValue = "Values";
$langPlatformAdministration = "Platform Administration";
$langPoweredBy = "Powered by";
$langPrevious = "Previous";
$langPreviousPage = "Previous Page";
$langProgInModuleTitle = "Your progression in this module";
$langProgress = "Progress";
$langQuestion = "Question";
$langQuitViewer = "Back to list";
$langRawHasBeenChanged = "Minimum raw to pass has been changed";
$langRemove = "Remove";
$langRename = "Rename";
$langRoot = "Root";
$langSCORMTypeDesc = "SCORM conformable content";
$langScore = "Score";
$langScormIntroTextForDummies = "Imported packages must consist of a zip file and be SCORM 2004 or SCORM 1.2 conformable.";
$langSecondShort = "sec.";
$langSize = "Size";
$langStartModule = "Start Module";
$langStatsOfLearnPath = "Statistics";
$langStudent = "Student";
$langSwitchEditorToTextConfirm = "This command is going to remove the current text layout. Do you want to continue ?";
$langTextEditorDisable = "Disable text editor";
$langTextEditorEnable = "Enable text editor";
$langThisCourseDescriptionIsEmpty = "This course is presently not described";
$langTimeInLearnPath = "Time in learning path";
$langTo = "to";
$langTotalTimeSpent = "Total time";
$langTrackAllPath = "Learning paths tracking";
$langTrackAllPathExplanation = "Progression of users on all learning paths";
$langTrackUser = "User Tracking";
$langTracking = "Tracking";
$langTypeOfModule = "Module type";
$langUnamedModule = "Unamed module";
$langUnamedPath = "Unamed path";
$langUp = "Up";
$langUseOfPool = "This page allows you to view all the modules available in this course. <br /> Any exercise or document that has been added in a learning path will also appear in this list.";
$langUsedInLearningPaths = "Number of learning paths using this module : ";
$langUser = "User";
$langUsers = "Users";
$langView = "View";
$langViewMode = "View mode";
$langVisibility = "Visibility";
$langWiki = "Wiki";
$langWork = "Assignments";
$langWrongOperation = "Wrong operation";
$langYourBestScore = "Your best performance";
$langZipNoPhp = "The zip file can not contain .PHP files";
$lang_enroll = "Enrol";
$langimportLearningPath = "Import a learning path";

?>
