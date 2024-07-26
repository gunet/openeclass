<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

/* * ********************************************************
 * General Messages - Feel free to suit them in your needs
 * ******************************************************** */

$langSiteUrl = "URL of collaboration platform";
$langInstall = "Installation of collaboration platform";

$langEclass = "Collaboration platform";

$langTeacher = "Coordinator";
$langOfTeacher = "Coordinator";
$langTeachers = "Coordinators";
$langOfTeachers = "Coordinators";

$langsTeacher = "coordinator";
$langsOfTeacher = "coordinator";
$langsTeachers = "coordinators";
$langsOfTeachers = "coordinators";
$langCTeacher = "COORDINATOR";

$langConsultant = "Consultant";
$langOfConsultant = "Consultant";
$langConsultants = "Consultants";
$langOfConsultants = "Consultants";

$langsTeacher = "consultant";
$langsOfTeacher = "consultant";
$langsTeachers = "consultants";
$langsOfTeachers = "consultants";

$langCourseAdmin = "Admin";
$langOfCourseAdmin = "Admin";
$langCourseAdmins = "Administrators";
$langOfCourseAdmins = "Administrators";

$langsCourseAdmin = "Admin";
$langsOfCourseAdmin = "Admin";
$langsCourseAdmins = "administrators";
$langsOfCourseAdmins = "administrators";

$langCourseAdminTeacher = "Admin - Coordinator";
$langOfCourseAdminTeacher = "Admin - Coordinator";
$langCourseAdminTeachers = "Administrators - Coordinators";
$langOfCourseAdminTeachers = "Administrators - Coordinators";

$langsCourseAdminTeacher = "admin - coordinator";
$langsOfCourseAdminTeacher = "admin - coordinator";
$langsCourseAdminTeachers = "administrators - coordinators";
$langsOfCourseAdminTeachers = "administrators - coordinators";

$langEditor = "Assistant coordinator";
$langOfEditor = "Assistant coordinator";
$langsEditor = "assistant coordinator";
$langsOfEditor = "assistant coordinator";

$langCourseReviewer = "Supervisor";
$langOfCourseReviewer = "Supervisor";
$langsCourseReviewer = "supervisor";
$langsOfCourseReviewer = "supervisor";

$langGroupTutor = "Team manager";
$langsOfGroupTutor = "team manager";

$langStudent = "Member";
$langOfStudent = "Member";
$langStudents = "Members";
$langOfStudents = "Members";
$langCStudent = "MEMBER";
$langCStudent2 = "MEMBER";

$langsStudent = "member";
$langsOfStudent = "member";
$langsStudents = "members";
$langsOfStudents = "members";
$langsOfStudentss = "members";
$langsstudent_acc = "member";

$langGuest = "Guest User";
$langGuests = "Users Visitors";

$langCourse = "Cooperation";
$langCourses = "Partnerships";
$langOfCourses = "Collaborations";
$langOfCourse = "Cooperation";

$langsCourse = "cooperation";
$langsCourses = "collaborations";
$langsOfCourse = "cooperation";
$langsOfCourses = "collaborations";
$langCourseS = "collaboration";
$langMyCourses = "My collaborations";

$langFaculty = "Category";
$langOfFaculty = "Category";
$langOfFaculties = "Categories";
$langFaculties = "Categories";

$langsFaculty = "category";
$langsOfFaculty = "category";
$langsFaculties = "categories";

$langpre = "Undergraduate";
$langpost = "Postgraduate";
$langother = "Other";

$langInfoAbout = "The $siteName platform is a complete Electronic Collaboration Management System. It follows the philosophy of open source software and supports the Asynchronous Distance Learning service without limitations and commitments. Access to the service is done using a simple web browser without requiring specialized technical knowledge.";


$langOfUserS = "user";






$langIntro = "The <strong>$siteName</strong> platform is an integrated $langOfCourses Electronic Management System and supports the Asynchronous Distance Learning Service at <a href=\"$InstitutionUrl\" target=\"_blank\" class=mainpage>$Institution< /a>.";
$langTotalCourses = "Number of $langsOfCourses";
$langInCourses = "in $langsCourse";
$langInCoursesL = "in $langsCourse";

// For the logged-out user:
$langUserWithRights = "User with rights to create collaborations";
$langUserWithNoRights = "User without partnership creation rights";
$langUsersWithTeacherRights = "Users with permissions to create collaborations";
$langUsersWithNoTeacherRights = "Users without partnership creation rights";
$langWithRights = "with the right to create collaborations";
$langWithCourseCreationRights = "creating collaborations";
$langWithNoCourseCreationRights = "not creating collaborations";
$langInfoEnableCourseRegistration = "User can subscribe to $langsCourses";


/* * ******************************************
 * addadmin.php
 * ******************************************* */
$langHelpPowerUser = "The admin assistant manages $langsCourses and users";

/* * **************************************************
 * admin.php
 * ************************************************** */
// index
$langSpeeSubscribe = "Register as a $langOfCourse Administrator";
$langMonthlyReportInfo = "Aggregates (partnerships, users) of the last 12 months are presented.";
$langListCours = "List $langOfCourses / Actions";
$langProfReg = "Register $langOfTeacher";
$langProfOpen = "Requests $langOfTeachers";
$langUserOpen = "Requests $langOfStudents";
$langAdminProf = "Manage $langOfTeachers";
$langAdminCours = "Manage $langsOfCourses";
$langOpenRequests = "Open applications " . $langsOfTeachers;
$langNoOpenRequests = "No open applications found " . $langsOfTeachers;
$langLastLesson = "Last $langsCourse created:";
$langLastProf = "Last entry $langsOfTeacher:";
$langLastStud = "Last registration $langsOfStudent:";
$langTeacherTutorials = "Helpful Guides $langsOfTeacher";
$langStudentTutorials = "Useful Guides $langsOfStudent";
$langTeacherStudentTutorials = "Useful Guides $langsOfTeacher - $langsOfStudent";
$langAdminTutorials = "Useful Guides Administrator";
$langDisableModulesHelp = "You can choose which of the following
subsystems you wish to disable from all its $langsCourses
platform.";
$langDefaultModulesHelp = "The following subsystems are activated ex
definition in the platform's new $langsCourses when creating them.";
$langAutoEnrollCourse = "Auto-subscribe to $langsCourses";
$langAutoEnrollDepartment = "Automatic enrollment in all departments $langsCourses";
$langActivityCourse = 'Partnerships-Activities';
$langPopularCourse = "Popular $langCourses";

// Stat
$langNbProf = "Number " . $langsOfTeachers;
$langNbStudents = "Number " . $langsOfStudents;
$langNbCourses = "Number $langsOfCourses";
$langRestoreCourse = "Recovery $langOfCourse";
$langStatCour = "Quantitative data $langsOfCourses";
$langNumCourses = "Number $langsOfCourses";
$langNumEachCourse = "Number $langsOfCourses per $langsFaculty";
$langNumEachLang = "Number $langsOfCourses per language";
$langNunEachAccess = "Number $langsOfCourses per access type";
$langNumEachCat = "Number $langsOfCourses per type $langsOfCourses";
$langPopularCourses = "$langCourses with most $langUsersS";
$langMultipleCourseUsers = "Users signed up to most collaborations";
$langAltAuthStudentReq = "Enable $langsOfStudent request linked to alternative authentication method";
$langDisableEclassStudReg = "Disable $langsOfStudent record";
$langDisableEclassProfReg = "Disable $langsOfTeacher registration";
$langDisableEclassStudRegType = "Ability to register $langsOfStudents";
$langDisableEclassProfRegType = "Ability to register $langsOfTeachers";
$langDisableEclassStudRegYes = "$langsStudents cannot register via platform";
$langDisableEclassProfRegYes = "$langsTeachers cannot register via platform";
$langDisableEclassStudRegNo = "$langsStudents can register via platform";
$langDisableEclassStudRegViaReq = "$langsStudents can register by application";
$langDisableEclassProfRegNo = "$langsTeachers can register via application";

// listcours
$langOpenCourse = "Open $langsCourse";
$langClosedCourse = "Closed $langsCourse";
$langClosedCourses = "Closed $langsCourses";
$langInactiveCourse = "Inactive $langsCourse";

// quotacours
$langQuotaAdmin = "Storage Management $langOfCourse";

// addfaculte
$langFaculteAdd = "Addition $langOfFaculty";
$langFaculteDel = "Deletion $langOfFaculty";
$langFaculteEdit = "Data processing $langOfFaculty";
$langFaculteIns = "Data Entry $langOfFaculty";

// addusertocours
$langListRegisteredStudents = "List of Enrollees " . $langOfStudents;
$langListRegisteredProfessors = "List of Enrollees " . $langOfTeachers;
$langErrChoose = "An error occurred in the selection $langsOfCourse!";
// delcours
$langCourseDel = "Deletion $langsOfCourse";
$langCourseDelSuccess = "$langsCourse deleted successfully!";
$langCourseDelConfirm = "Confirm Delete $langOfCourse";
$langCourseDelConfirm2 = "You definitely want to delete $langsCourse";
$langNoticeDel = "NOTE: Deleting $langsOfCourse will also delete registered " . $langsOfStudentss . " from $langsCourse, $langsOfCourse mapping to $langsFaculty, and all $langsOfCourse stuff.";

// editcours
$langCourseInformationText = "Edit description $langsOfCourse";
$langCourseEdit = "Processing $langOfCourse";
$langCourseImage = "Photo $langOfCourse";
$langCourseStatus = "Condition $langOfCourse";
$langStatsCourse = "Statistics $langOfCourse";

// course_info.php
$langCourseEditSuccess = "$langsOfCourse details changed successfully!";
$langCourseInfoEdit = "Change $langsOfCourse elements";
$langBackCourse = "Return to the $langsOfCourse home page";
$langCourseFormat = "Format $langsOfCourse";
$langWithCourseUnits = "$langCourse with modules (weekly, thematic)";
$langsCourseSharing = "$langsOfCourse sharing on social networks";
$langsCourseRating = "$langsOfCourse assessment";
$langCourseComment = "$langsOfCourse annotation";
$langsCourseAnonymousRating = "of $langsOfCourse comments by anonymous users";
$langForumNotifications = "Update $langsOfStudents";
$langActivateForumNotifications = "Enable update $langsOfStudents (via email)";
$langDisableForumNotifications = "Disable updating $langsOfStudents (via email)";

// listreq.php
$langOpenProfessorRequests = "Open Applications " . $langOfTeachers;
$langProfessorRequestClosed = "His application " . $langsOfTeacher . " closed!";
$langemailbodyBlocked = "Your application to register on the platform " . $siteName . " turned down.";

// mailtoprof.php
$langProfOnly = "to the " . $langsTeachers . " ";
$langStudentsOnly = "at " . $langsOfStudentss . " ";

// searchcours.php
$langSearchCourse = "search $langOfCourses";

// statuscours.php
$langCourseStatusChangedSuccess = "Access type of $langsOfCourse changed successfully!";
$langCourseStatusChange = "Change access type $langsOfCourse";

// authentication
$langMultiMoveCourseInfo = "Select the new $langsFaculty to move the $langsCourses to.";
$langMultiRegCourseUser = "Bulk enrollment of users in $langsCourses";
$langMultiMoveCourses = "Bulk transfer $langsOfCourses";
$langConfirmMultiMoveCourses = "Confirm transfer of $langsOfCourses to another department.";
$langCourseCodes = "Codes $langsOfCourses";
$langAskManyUsersToCourses = "$langsCourses should exist and users should already have an account on the platform to enroll in them. Type the usernames and passwords of $langsOfCourses on separate lines.";
$langMultiRegUserInfo = "<p>Enter in the field below a directory of
the user details, one line per user you wish to create.
</p>
<p>The order of the elements is defined in the field before it
area, and the possible tags are:</p>
<ul><li>
<li><tt>first</tt>: Name</li>
<li><tt>last</tt>: Last Name</li>
<li><tt>email</tt>: E-mail address</li>
<li><tt>id</tt>: Registration number</li>
<li><tt>phone</tt>: Phone</li>
<li><tt>username</tt>: Username</li>
<li><tt>password</tt>: User password</li>
</ul>
<p>If you want users to automatically enroll in some $langsCourses, please add
their codes at the end of the line after the fields you have defined. The
e-mail is optional - if you want to omit it, put a instead
hyphen (-). Lines starting with # are ignored. If you do not set the name
user of users, they will automatically get usernames consisting of
prefix that you can set below and a serial number. If you don't specify
the password, a random password is automatically set that is different for each account</p>";
$langMultiRegCourseInvalid = "User %s: invalid $langsOfCourse code '%s'";
$langSearchCourses = "Search $langsOfCourses";

// other
$langLessonCode = "Password $langsOfCourse";

// unregister
$langConfirmDeleteQuestion2 = "from $langsCourse";
$langWasCourseDeleted = "deleted by $langCourse";
$langErrorUnreguser = "Error unsubscribing user from $langsCourse";


// eclassconf
$langDefaultQuota = "New $langsOfCourses storage";
$langStudentUploadWhitelist = "Allowed file types for $langsOfStudentss";
$langTeacherUploadWhitelist = "Allowed file types for $langsTeachers";
$langIndexingRemain = "$langCourses remaining to be indexed";
$langReqRegUser = "$langsOfStudent registration request";
$langCourseSettings = "$langsOfCourses settings";
$langCourseOfflineSettings = "Download $langsOfCourse";
$langCourseOfflineLegend = "$langsStudents can download $langsCourse to their computer.";
$langUserConsent = "Consent $langsOfStudent";
$langUnsubscribeCourse = "Disable collaboration opt-out";
$langClassInfoTitle = "Cooperation details";
$langStuNum = "Number of Members";
$langLectNum = "Number of Partnerships";
$langLectHours = "Collaboration time";
$langTotalHours = "total time";
$langEmptyGoal ="
Complete all the cooperative objectives you have created.";
$langConfirmDeleteGoal = "Are you sure you want to remove this specific cooperative goal?";


// common documents
$langExplainCommonDocs = "The file you upload will be accessible from all $langsCourses via the 'Documents' subsystem";

/* * ********************************************************
 * agenda.php
 * ******************************************************** */
$langPreviousMonth = "Previous $langMonth";
$langNextMonth = "Next $langMonth";
$langAgendaCourseEvent = "Fact $langsOfCourse";


/* * *********************************************************
 * announcements.php
 * ********************************************************** */
$langRegUser = "registered users of $langsOfCourse";
$professorMessage = "$langsOfTeacher message";
$langCourseAnnouncements = "$langsOfCourse announcements";
$langLinkUnsubscribe = "You received this message because you are enrolled in $langsCourse '%s'. If you do not wish to receive further e-mails from this particular $langsCourse, click";
$langEmailUnsubscribe = "$langsOfCourse Notifications";
$langInfoUnsubscribe = "You can set up notifications from the following $langsCourses. If you don't want to
receive e-mails from some $langsCourse, deselect it and click 'Submit'. <br />(Note: You are not unsubscribed from $langsCourse).";
$langEmailUnsubSuccess = "You will no longer receive emails from $langsCourse '%s'";
$langEmailFromCourses = "Receive emails from my $langsCourses";
$langEmailUnsubscribeWarning = "You have disabled receiving messages from the platform.
You cannot configure downloading from specific $langsCourses before re-enabling it.";
$langAnnHasPublished = "An announcement has been posted on $langsCourse";

/* * *****************************************
 * archive_course.php
 * ***************************************** */
$langCreateDirCourseBase = "Creating the directory to retrieve the bases of $langsOfCourses";
$langCopyDirectoryCourse = "Copy the $langsOfCourse files";
$langBUCourseDataOfMainBase = "Backup of $langsOfCourse data";
$langBackupOfDataBase = "Backup of the $langsOfCourse database";


/* * **********************************************************
 * chat
 * **************************************************************** */
$langUntitledChat = "Free chat $langsOfCourse";

/* * ***************************************************************
 * copyright.php
 * **************************************************************** */
$langCopyright = "Copyright Information";
$langUsageTerms = 'Terms of use';

/* * *****************************************************
 * course_description.php
 * ***************************************************** */
$langAddUnit = "Add new $langCourseS unit";
$langCourseProgram = "Description $langOfCourse";
$langCourseDescription = "Cooperation Description";
$langThisCourseDescriptionIsEmpty = "$langsCourse has no description";
$langQuestionPlan = "Question to the moderator";

$titreBloc = array(
    "Content $langsOfCourse",
    'Learning objectives',
    'Bibliography',
    'Teaching methods',
    'Evaluation methods',
    'Prerequisites',
    'Moderators',
    'target group',
    'Recommended books',
    'More');
$titreBlocNotEditable = array(TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, FALSE);

/* * ******************************************************
 * course_home.php
 * ******************************************************* */
$langIdentity = "ID card $langOfCourse";
$langCourseS = $langsCourse;
$langCitation = "Bibliographic reference $langsOfCourse";
$langUserEmailNotification = "Receive $langsOfCourse notifications by email.";
$langNoUserEmailNotification = "You do not receive $langsOfCourse email notifications.";
$langNoUserEmailLegend = "User does not receive $langsOfCourse email notifications.";
$langCourseInvalidDepartment = "$langsCourse is in a section where it doesn't
now allowed to create $langsOfCourses.";
$langCourseInvalidDepartmentPrompt = "Please %s move it
via $langsOfCourse %s settings.";
$langReadMore = "More";
$langReadLess = "Less";
$langNoInfoAvailable = "No information available";
$langDownloadCourse = "Download the collaboration";
$langConfEnableMailNotification = "Want to enable receiving $langsOfCourse email notifications?";
$langConfDisableMailNotification = "Do you want to opt out of receiving $langsOfCourse email notifications?";
$langOfNotifications = "notifications";


/* * *******************************************
 * course_info.php
 * ******************************************* */
$langCourseIden = "ID card $langOfCourse";
$langCloneCourse = "Clone $langsOfCourse";
$langModifDone = "$langsOfCourse settings changed.";
$langDelCourse = "Delete $langsOfCourse";
$langCourseTitle = "Title $langOfCourse";
$langConfTip = "Select the access type of $langsOfCourse from users.";
$langNoCourseTitle = "You did not type the title of $langsOfCourse";
$langCourseWeeklyFormatNotice = "For weekly display you must select at least $langsOfCourse start date";
$langCourseUserRequests = "Requests to register users on $langsCourse";
$langCourseUserRequestsDisabled = "User registration requests do not exist for non-closed $langsCourses.";
$langUsersListAccess = "Show users of $langsOfCourse";
$langUsersListAccessInfo = "only applies to registered $langsOfCourse users";

// delete_course.php
$langTheCourse = "The $langsCourse";
$langHasDel = "It has been deleted";
$langByDel = "Deleting $langsCourse will permanently delete all its contents and all " . $langsOfStudentss . " written to it (they will not be deleted from other $langsCourses).";
$langByDel_A = "Do you really want to delete $langsCourse:";
$langTipLang = "Select the language in which $langsOfCourse messages will be displayed.";

// deluser_course.php
$langConfirmDel = "Confirm delete $langsOfCourse";
$langUserDel = "You are about to delete all " . $langsOfStudentss . " from $langsCourse (they will not be deleted from other $langsCourses).<p>Do you really want to proceed with deleting them from $langsCourse?";

// refresh course.php
$langRefreshCourse = "Refresh $langsOfCourse";
$langRefreshInfo = "In order to prepare $langsCourse for a new group " . $langsOfStudents . " you can delete the old content.";
$langRefreshInfo_A = "Choose which actions you want to take place.";
$langUserDelCourse = "Delete users from $langsCourse";
$langUserDelNotice = "Note: Users will not be deleted from other $langsCourses";
$langAnnouncesDel = "Delete $langsOfCourse announcements";
$langAgendaDel = "Delete entries from the $langsOfCourse calendar";
$langHideDocuments = "Hide $langsOfCourse docs";
$langHideWork = "Hide $langsOfCourse assignments";
$langHideExercises = "Hide $langsOfCourse exercises";
$langDelAllWorkSubs = "Delete $langsOfCourse assignment submissions";
$langDelWallPosts = "Delete $langsOfCourse wall posts";
$langDelBlogPosts = "Delete $langsOfCourse blog posts";
$langRefreshSuccess = "Refresh of $langsOfCourse was successful. The following actions were performed:";
$langUsersDeleted = "Users deleted from $langsCourse";
$langAnnDeleted = "Announcements deleted by $langsCourse";
$langAgendaDeleted = "Log entries were deleted by $langsCourse";
$langBlogPostsDeleted = "Blog posts deleted by $langsCourse";
$langWallPostsDeleted = "Wall posts deleted by $langsCourse";


/* * ***************************************************
 * contact.php
 * **************************************************** */
$langContactProf = "Contact $langsTeachers";
$langEmailEmpty = "Your email address is empty.
To be able to contact $langsOfTeacher, you must have set
your address so you can receive a reply. You can set the
your address from <a href='%s'>«Change my profile»</a> in
your portfolio page.";

$langContactMessage = "Type your message text. This will be emailed to the $langsTeachers responsible for $langsOfCourse.";
$langSendingMessage = "Request sent to $langsTeachers of $langsOfCourse:";
$langContactIntro = "Platform user $siteName named %s (%s <%s>) sent you the registration request below.";
$langContactIntroFooter = "To manage registration requests in $langsCourse click ";

$langNonUserContact = "To contact the responsible $langsTeachers
of $langsOfCourse, you should have an account in the system and
to be logged in. Please visit <a href='%s'>home page</a>.";
$langInfoAboutRegistration = "The $langsCourse you selected is closed. To register, you must
complete the application below, which will be recorded and sent to the $langsOfCourse administrators.";
$langLabelCourseUserRequest = "Apply to enroll in $langsCourse";
$langRequestReasons = "State the reasons you wish to enroll in $langsCourse.";

/* * **************************************************
 * create_course.php
 * *************************************************** */
$langDescrInfo = "Short description of $langsOfCourse";
$langTargetFac = "The $langsFaculty that $langsCourse belongs to";
$langDoubt = "If you do not know the code of $langsOfCourse consult";
$langExFac = "* If you wish to create a $langsCourse in a different $langsFaculty than the one you belong to, then please contact
the Asynchronous Distance Learning Group";
$langErrorDir = "The subdirectory of $langsOfCourse was not created and $langsCourse will not work!<br><br>Check the access rights of the <em>courses</em> directory.";
$langSubsystems = "Select the subsystems you want to enable for your new $langsCourse:";
$langLanguageTip = "Choose which language the $langsOfCourse pages will be displayed in";
$langDelAdmin = "Discussion area for anything related to $langsCourse";
$langVideoLinks = "Videotaped $langCourses";
$langModifyInfo = "Manage $langOfCourse";
$langVideoText = "Example of a RealVideo file. You can upload any type of audio or video file (.mov, .rm, .mpeg...) as long as the " . $langsStudents . " they have the corresponding plug-in to view it";
$langIntroductionText = "$langsOfCourse introductory text. Replace it with your own by clicking <strong>Change</strong>.";
$langJustCreated = "You have just successfully created $langsCourse titled ";
$langCreateCourseNotAllowedNode = "You selected invalid $langsFaculties. Please try again.";


$langAttendanceAbsencesFrom2 = "required by $langsOfCourse.";
$langAttendanceUsers = "Successfully updating $langsOfStudents count.";
$langAttendanceActCour = "Activity $langsOfCourse";
$langAttendance6Months = "Final semester $langsStudents are participating";
$langAttendance3Months = "Final term $langsStudents are participating";
$langAttendanceAllMonths = "All registered $langsStudents participate";
$langNewAttendance2 = "Create new presentation for $langsCourse";


$langGradebookActCour = "Activity $langsOfCourse";
$langGradebookAllBetweenRegDates = "Everyone who has signed up for $langsCourse during the time period below";
$langGradebookAssignSpecific = "The task is specifically for $langsStudents";
$langStudLastSemester = "$langStudents last quarter only";
$langNoRegStudent = "There are no $langsStudents participating in the grade. You can add users by clicking";
$langNoStudents = "There are no $langsStudents in the range you selected";
$langNoStudentsInAttendance = "There are no $langsStudents in the portfolio. You can add users by clicking";
$langUsersGradebook = "$langStudents score";
$langNoGradebook = "You are not participating in graded $langsCourses.";
$langNewGradebook2 = "Create new score for $langsCourse";
$langChangeGradebook2 = "(select active grade for $langsCourse)";
$langSpecificUsers = "specifically $langsStudents";



// Groups
$langGroupManyUsers = "<strong>Note</strong>: Users should already be registered with $langsCourse.";
$langUsersNotRegistered = "The following users are not registered with $langsCourse";

//neos odhgos dhmiourgias mathimaton
$langEnterMetadata = "You can change the $langsOfCourse settings through the 'Management $langOfCourse'";
$langCreateCourse = "$langsOfCourse build wizard";
$langCreateCourseStep2Title = "Additional information $langsOfCourse";
$langcourse_objectives = "Goals of $langsOfCourse";
$langFinalize = "Creation $langsOfCourse";
$langCourseCategory = "The class that $langsCourse belongs to";
$langProfessorsInfo = "$langsOfTeachers names of $langsOfCourse separated by commas (eg<i>Nikos Tzikopoulos, Kostas Adamopoulos</i>)";
$langClosedCourseShort = "Only those on the $langsOfCourse User List have access to $langsCourse";
$langCourseInactive= "Only $langsTeachers of $langsOfCourse have access to $langsCourse";
$langCourseInactiveShort = "Inactive $langsCourses";
$langCourseActiveShort = "Active $langsCourses";
$langAlertTitle = "Please fill in its title $langsOfCourse!";
$langAlertProf = "Please fill in its coordinator $langsOfCourse!";

// Admin mutiple course creation
$langMultiCourse = "Mass creation $langsOfCourses";
$langMultiCourseInfo = "Enter a list of them in the area below
titles of the $langsOfCourses you wish to create, with each
title on a separate line. If you want it to be added automatically
moderator in $langsCourse, enter first name or username
of after the title, separated by the character \"|\".";
$langMultiCourseTitles = "Titles / Moderators $langOfCourses";
$langMultiCourseData = "Data $langOfCourses";
$langMultiCourseCreated = "$langsCourse was created";



/* * ***********************************************
 * dropbox.php
 * *********************************************** */
$langNewCourseMessage = "New message $langsOfCourse";
$langDropBoxIncompatible = "Caution! The 'File Exchange' subsystem probably
not properly recovered due to incompatibility. Check table ids
dropbox_file at the base of $langsOfCourse.";
$langDropboxFreeSpaceConfirm = "This action will delete the attachments in the oldest $langsOfCourse messages to free up %s space! Users will no longer be able to download them!";




/* * *********************************************
 * faculty.php
 * ********************************************* */
$langCodeFaculte1 = "Code $langOfFaculty";
$langAddFaculte = "Addition $langOfFaculties";
$langProErase = "There are taught $langsCourses!";
$langNoErase = "Unable to delete $langOfFaculty.";

/* * *********************************************
 * hierarchy.php
 * ********************************************* */

$langHierarchyActions = "$langOfFaculties Directory - Actions";
$langNodeAdd = "Selection $langOfFaculty";
$langNodeDel = "Deletion $langOfFaculty";
$langNodeEdit = "Processing $langOfFaculty";
$langNodeCode1 = "Code $langOfFaculty";
$langNodeName = "Name $langOfFaculty";
$langNodeDescription = "Description $langOfFaculty";
$langNodeParent2 = "(which the $langsFaculty you are editing will belong to)";
$langNodeAllowCourse = "Register $langsOfCourses";
$langNodeAllowCourse2 = "(check if $langsCourses can belong to $langsFaculty)";
$langNodeAllowUser2 = "(check if users can belong to $langsFaculty)";
$langNodePublic2 = "$langsFaculty will be displayed for all users";
$langNodeSubscribed2 = "$langsFaculty will only be visible to registered users";
$langNodeHidden2 = "$langsFaculty will remain hidden from all users";
$langNodeProErase = "There are $langsCourses or users owned by this node ($langsFaculty) or its children!";
$langNodeNoErase = "Unable to delete $langsOfFaculty.";
$langNodeErase = "$langsFaculty has been deleted!";
$langReturnToEditNode = "Back to editing $langsOfFaculty";

/* * *************************************************************
 * grades.php
 * ************************************************************** */
$m['grades'] = "Grading";

/* * ***********************************************************
 * group
 * *********************************************************** */


// Group Properties
$langGroupAllowStudentRegistration = "The " . $langsStudents . " are allowed to register";
$langGroupAllowStudentUnregister = "The " . $langsStudents . " are allowed to unsubscribe";


// Group space
$langNoGroupStudents = "Not registered " . $langsStudents . " ";
$langGroupNoneMasc = "There are no $langsStudents in the group";
$langGroupFilledGroups = "The user groups are populated from " . $langsOfStudentss . " found in the directory «Users».";

// group - email
$langProfLesson = "Moderator of $langsOfCourse";

/* * ***************************************************
 * guest.php
 * *************************************************** */

$langGuestSurname = "$langOfCourse";
$langGuestLoginLabel = "$langsOfCourses guest accounts";
$langGuestLoginLinks = "Enabling and adding links to the $langsOfCourses directory";

/* * ******************************************************
 * gunet.php
 * ****************************************************** */
$infoprof = "An e-mail will be sent to you shortly from the Management Team of the $siteName Asynchronous Distance Learning Platform, with your account details.";
$profinfo = "The online platform $siteName has 2 alternative ways to register moderators";
$userinfo = "The $siteName online platform has 2 alternative ways to register users:";
$regprofldap = "Registration of moderators who have an account in the LDAP Directory Service of the institution they belong to";
$regldap = "Registration of users who have an account in the LDAP Directory Service of the institution they belong to";
$regprofnoldap = "Register coordinators who do not have an account in the Directory Service of the institution they belong to";
$regnoldap = "Register users who do not have an account in the Directory Service of the institution they belong to";
$mailbody1 = "\n$Institution\n\n";
$mailbody5 = "of $siteName ";
$mailbody6 = "as $langTeacher.";
$mailbody8 = "as $langStudent.";
$logo = "Asynchronous Distance Learning Platform $siteName";
$gunet = "Asynchronous Distance Learning Team $siteName";
$langAdminMessage = "Admin Message $siteName";

// contact.php
$introcontact = "You can contact the <strong>" . $siteName . "</strong> Platform Support Team in the following ways:";


/* * **********************************************************
 * course_tools.php
 * ********************************************************** */

$langExplanation_0 = "If you have created a page for your $langsCourse in HTML format (eg \"my_page.htm\") then you can use the form below to create a link to the $langsOfCourse menu (left). Your page is thus published (uploaded) to the platform and displayed along with the rest of the $langsOfCourse tools. <br/>";

/* * *************************************************************
 * php
 * ************************************************************* */
$langInvalidGuestAccount = "The $langsCourse for which the 'guest user' account was created no longer exists.";
$langAccountActivateMessage = 'The user with the following information wants the
reactivate his account:';
$langMyCoursesProf = "The $langsCourses I support";
$langMyCoursesUser = "The $langsCourses I'm taking";
$langNoCourses = "There are no $langsCourses";
$langCourseCreate = "Create $langsOfCourse";
$langListCourses = "$langCourses";
$langHierarchy = "$langOfFaculties Hierarchy";
$langUnregCourse = "Unsubscribe from $langsCourse";
$langCourseCode = "$langCourse (Code)";
$langWelcomeStud = "Choose «<strong>$langListCourses</strong>» to track available e-mails $langsCourses.";
$langWelcomeProf = "Choose «<strong>$langCourseCreate<strong>» to create your electronics $langsCourses.";
$langWelcomeStudPerso = "«<strong>$langListCourses</strong>» to track available e-mails $langsCourses.";
$langWelcomeProfPerso = "«<strong>$langCourseCreate</strong>» to create your electronics $langsCourses.";
$langCourseOptions = "Choices $langOfCourse";

/* * *********************************************************
 * install.php
 * ********************************************************* */

$langWarnHelpDesk = "Attention: to the \"Email Helpdesk\" moderator requests are sent for an account on the platform";
$lang_max_glossary_terms = "Term definitions are not allowed to appear on $langsOfCourses pages if the total number of terms in the glossary is greater than:";
$lang_email_required = "$langOfUserS email must be required";
$lang_email_verification_required = "Confirmation of $langOfUserS e-mail is mandatory";
$lang_dont_mail_unverified_mails = "Do not send e-mails to $langUsersS who have not confirmed their e-mail address";
$lang_am_required = "$langsOfStudent registration number is mandatory when registering";
$lang_dropbox_allow_student_to_student = "Allow $langsOfCourse messaging between $langsOfStudents in the subsystem 'Exchange of Messages'";
$lang_course_multidep = "Allow $langsCourses to belong to multiple $langsFaculties";
$lang_user_multidep = "Allow users to register in multiple $langsFaculties";
$lang_restrict_owndep = "Do not allow users to change section";
$lang_restrict_teacher_owndep = "Don't allow $langsOfCourses to be created by $langsTeachers in classes they don't belong to";
$lang_allow_teacher_clone_course = "Allow $langsOfCourses to be cloned from $langsTeachers";
$lang_disable_log_course_actions = "Disable logging of user actions within $langsCourses";
$lang_disable_log_system_actions = "Disable logging of user actions outside of $langsOfCourses";
$lang_course_metadata = "Postcomment $langOfCourses";


/* * ***********************************************
 * lessontools.php
 * ************************************************ */
$langAdministrationTools = "Management $langsOfCourse";
$langCourseTools = "Tools $langsOfCourse";

/* * ************************************************
 * link.php
 * ************************************************* */


/* * ***************************************************************
 * lostpass.php
 * *************************************************************** */

$langPassResetIntro = "
You have been asked to set a new password for your access to
distance learning platform $siteName.";
$langAccountNotFound3 = "giving your details such as name, email address, $langsFaculty, etc.";


/* * ****************************************************
 * manual.php
 * ***************************************************** */
$langFinalDesc = "Detailed description $siteName";
$langShortDesc = "Short description $siteName";
$langManS = "Handbook $langOfStudent";
$langManT = "Handbook $langOfTeacher";
$langIntroToCourse = "Introduction to Electronics $langCourse";
$langAdministratorCourse = "Electronics Management $langOfCourse";

/* * *******************************************************
 * open course.php
 * ******************************************************* */
$langSelectFac = "Selection $langOfFaculty";
$langDepartmentsList = "The following is the list of departments of the institution.
Select any of them to see the $langsCourses available in it.";
$langWrongPassCourse = "Incorrect password for $langsCourse";
$langAvCourses = "$langsCourses available";
$langAvCours = "available $langsCourse";
$m['open'] = "Open $langsCourses (Open Access)";
$m['closed'] = "Closed $langsCourses";
$m['nolessons'] = 'There are none available '.$langsCourses.'!';
$langNoCourses = "There are no $langsCourses available in this category!";
$m['code'] = "Password $langsOfCourse";
$m['mailprof'] = "To sign up for $langsCourse you need to email the instructor of $langsOfCourse
by clicking";
$m['here'] = " here.";
$m['unsub'] = "$langsCourse is closed and you will not be able to re-enroll.";

$langNotEnrolledToLessons = "You are not subscribed to $langsCourses";

/* * *********************************************************
 * forum.php
 * ********************************************************** */

$langBodyForumNotify = "A new topic has been added to $langsCourse";
$langAnonymousExplain = "
Users shown in a different color are not currently registered with $langsCourse, while those marked 'Anonymous' have been deleted from the system.";
$langPrivateNotice = "$langPrivateForum<br>Note: you must have cookies enabled to use private areas.";

/* * ***************************************************************
 * questionnaire.php
 * **************************************************************** */
$langCreateSurvey = 'Create a Collaborative Profile Survey';
$langSurveysActive = "Active Collaborative Profile Surveys";
$langSurveysInactive = "Inactive Partner Profile Surveys";
$langSurveyCreated = "Collaborative Profile Survey successfully created.";
$langSurveyDeleted = "Collaborative Profile Survey successfully deleted.";
$langSurveyDeactivated = "Collaborative Profile Survey successfully disabled.";
$langSurveyActivated = "Collaborative Profile Survey successfully activated.";
$langSurveyNone = "No Collaborative Profile surveys have been created for $langsCourse";
$langSurveyInactive = "The Affiliate Profile Survey has expired or has not yet been activated.";
$langCurrentCourse = "Current $langCourse";

//COLLES survey
$qcolles7 = "In this section I think critically about other members' ideas";
$qcolles9 = "In this section I explain my ideas to other members";
$qcolles10 = "In this section I ask other members to explain their ideas";
$qcolles11 = "In this section other members ask me to explain my ideas";
$qcolles12 = "In this section other members respond to my ideas";
$qcolles13 = "In this section the moderator stimulates my thinking";
$qcolles14 = "In this section the moderator encourages me to participate";
$qcolles15 = "In this section the moderator displays the correct discussion";
$qcolles16 = "In this section the moderator presents the critical view of ourselves";
$qcolles17 = "In this section the other members encourage my participation";
$qcolles18 = "In this section other members praise my contribution";
$qcolles19 = "In this section other members appreciate my contribution";
$qcolles20 = "In this section the other members understand my attempt at cooperation";
$qcolles21 = "In this section I make sense of other users' messages";
$qcolles22 = "In this section other members understand my messages";
$qcolles23 = "In this section I understand moderator messages";
$qcolles24 = "In this section the moderator understands my messages";
$lcolles4 = "Moderator support";
$lcolles5 = "Member support";
$scolles7 = "critique other users' ideas";
$scolles13 = "moderator stimulates my thinking";
$scolles14 = "the moderator encourages my participation";
$scolles15 = "the moderator displays the correct discussion";
$scolles16 = "the moderator projects the critical view of ourselves";
$scolles17 = "the other members encourage my participation";
$scolles18 = "other members praise my contribution";
$scolles19 = "other members appreciate my input";
$scolles20 = "the other members understand my attempt at cooperation";
$scolles21 = "I make sense of other users' messages";
$scolles22 = "other members understand my messages";
$scolles23 = "I understand the moderator's messages";
$scolles24 = "the moderator understands my messages";

$colles_desc = "The COLLES questionnaire (Constructive On Line Learning Environment Survey) is used to explore the views of the collaborative environment that
have been shaped by the participants. It consists of 24 questions. Each question has a number from 1 (Almost Never),
2 (Rarely), 3 (Sometimes), 4 (Often), up to 5 (Almost always)";
$langCollesLegend = "1 (Almost Never), 2 (Rarely), 3 (Sometimes), 4 (Often), 5 (Almost Always)";
$colles_detail_answer = "Here you can see in aggregate what the user answered";

//ATTLS survey
$question4 = "
The most important part of my collaboration is learning to understand people who are very different from me";
$langCKW = "The user learns connected with the other members: ";
$langSKW = "The user learns separately from other members: ";
$langCKW_SKW = "The user learns both connected and separated from other members: ";
$lang_ckw_skw_chart = "Here is a summary of how many users learn online, how many learn separately, and how many learn both ways";

// polls
$langPollShowResults = "Show results in $langsOfStudentss";
$langActivateMulSubmissions = "Enable multiple submissions by $langsOfStudentss";


/* * **********************************************************
 * registration.php
 * *********************************************************** */

$langCourseName = "Title $langOfCourse";
$langNoCoursesAvailable = "No $langsCourses available";
$langUserDetails = "Sign up $langOfStudent";
$langPersonalSettingsMore2 = "
to select from the '$langCourses' option the $langsCourses you wish to attend.";
$langYourRegTo = "The $langsOfCourses directory contains you";
$langLessonName = "Name $langsOfCourse";

// profile.php
$langProfileInfoProfs = "display in $langsTeachers";
$langSumCoursesEnrolled = "$langCourses I'm </br>taking";
$langSumCoursesSupport = "$langCourses I </br>support";
$langShowSettingsInfo = "is about showing to other users (except $langsOfTeachers)";

// user.php
$langInC = "in this $langsCourse";
$langRegYou = "has enrolled you in $langsCourse";
$langUserDeleted = "User deleted from $langsCourse";
$langAddHereSomeCourses = "<p>To enroll / unenroll in / from a $langsCourse,
first select your $langsFaculty and then select / deselect $langsCourse.<br>
<p>To register your preferences press 'Submit changes'</p><br>";
$langDeleteUser = "User unsubscribe confirmation";
$langDeleteUser2 = "from $langsCourse";
$langAskUser = "Search for the user you want to add. The user must have an active account on the platform to subscribe to $langsCourse.";
$langAskManyUsers = "<strong>Note</strong>:<br /> Users should
already have an account on the platform to enroll in $langsCourse.
Enter usernames or registration numbers on separate lines.";
$langNoUsersFound = "No user was found with the information you provided, or the user already exists in $langsCourse.";
$langAdded = " added to $langsCourse.";
$langAddError = "Error! User not added to $langsCourse. Please try again or contact your system administrator.";
$langCourseNotExist = "Code $langsOfCourse does not exist.";
$langUsersAlreadyRegistered = "The following users are already registered in $langsCourse:";
$langUsersRegistered = "The following users have been added to $langsCourse:";
$langNotifyRegUser1 = "You have successfully registered for $langsCourse ";
$langNotifyRegUser2 = " by the $langsOfCourse admin.";

// search_user.php

$langAdminDefinition = "Administrator (right to change the contents of $langsOfCourses)";
$langDeleteUserDefinition = "Delete (delete from <strong>current</strong> user directory $langsOfCourse)";
$langNoTutor = "is not a moderator on this $langsCourse";
$langYesTutor = "is a moderator on this $langsCourse";
$langGiveRightEditor = "$langsOfEditor permission";
$langGiveRightAdmin = "$langsOfTeacher right";
$langGiveRightCourseReviewer = "$langsOfCourseReviewer privilege";
$langRemoveRightEditor = "Remove permission $langsOfEditor";
$langRemoveRightAdmin = "Remove privilege $langsOfTeacher";
$langRemoveRightCourseReviewer = "Remove privilege $langsOfCourseReviewer";
$langUserAlreadyRegistered = "A user with the same first / last name is already registered in this $langsCourse.
You cannot rewrite him/her.";
$langAddedToCourse = "is already written in the platform but not in this $langsCourse. Now it's done.";
$langChoiceLesson = "Selection $langsOfCourses";
$langRegCourses = "Sign up to $langsCourse";
$langRegEnterCourse = "Register and login to $langsCourse";
$langChoiceDepartment = "Selection $langOfFaculty";
$langCoursesRegistered = "Your registration for the $langsCourses you selected was successful!";
$langNoCoursesRegistered = "<p>You have not selected $langsCourse to enroll in.</p><p> You can enroll in $langsCourse, the
next time you enter the platform.</p>";
$langLastUserVisits = "Recent visits to $langsCourse";
$langReqRegProf = "User registration request (with $langsOfCourses create permissions)";
$langNewProf = "Εισαγωγή στοιχείων νέου λογαριασμού $langsOfTeacher";
$profsuccess = "New $langsOfTeacher account created successfully!";
$ldaplastpage = "Previous Page";
$mailsubject = "Application " . $langOfTeacher . " - Asynchronous Distance Learning Service";
$mailsubject2 = "Αίτηση " . $langOfStudent . " - Application";
$searchuser = "Search Moderators / Users";
$langAsProf = "as $langsTeacher";
$langCourseVisits = "Visits per $langsCourse";
$langDurationVisitsPerCourse = "Duration of participation per $langsCourse";

// user registration
$langStudentCannotRegister = "To register as a user with $langsOfStudent rights, contact the platform administrators.";
$langTeacherCannotRegister = "To register as a $langsOfTeacher user, please contact the platform administrators.";
$langUserAccount = "Account $langOfStudent";
$langProfAccount = "Account $langOfTeacher";
$langNewUserAccountActivation = "Account Activation $langOfStudent";
$langNewProfAccountActivation = "$langOfTeacher Account Activation";



// list requests
$langTeacherRequestHasDeleted = "$langsOfTeacher  request has been deleted!";
$langGoingRejectRequest = "You are about to reject the $langsOfTeacher request with:";
$langTeacherRequestHasRejected = "$langsOfTeacher request was denied";
$langRequestHasRejected = "Your request to register on the $siteName platform has been rejected.";
$langCourseRegistrationDate = "$langsCourse enrollment date";
$langStudentParticipation = "$langCourses in which the user participates";
$langNoStudentParticipation = "User is not participating in any $langsCourse";
$langUserMergeForbidden = "Cannot merge $langsOfTeachers with non-$langsTeachers. Please choose another user.";
$langUnregFirst = "You should first delete the user from the following $langsCourses:";
$langUnregTeacher = "Is " . $langsTeacher . " in the following $langsCourses:";
$langOtherDepartments = "Sign up to $langsCourses other departments/schools";
$langNoLessonsAvailable = "There are no $langCourses Available.";


// formuser.php
$langUserRequest = "$langOfStudent Account Creation Request";
$langUserOpenRequests = "
$langOfStudents open applications";
$langWarnReject = "You are about to reject the request $langsOfStudent";
$langNewUserDetails = "User Account Details-$langOfStudent";
$langInfoProfReq = "If you wish to have access to the platform with $langsOfCourses creation rights, please complete the application form below. The application will be sent to the responsible administrator who will create the account and send you the details via email.";
$langUserSuccess = "New account $langOfStudent";
$usersuccess = "Creating a new account " . $langsOfStudent . " carried out successfully!";
$langAsUser = "(Account $langOfStudent)";
$langMailVerificationSubject = "Account Creation Request Confirmation $siteName";
$langMailChangeVerificationSubject = "Account confirmation e-mail $siteName";
$langMailVerificationSuccess2 = "For this purpose, check your email where a confirmation email will have been sent to you. Once the confirmation is complete, a second e-mail will be sent to you
from the $siteName Asynchronous Distance Learning Platform Management team,
with your details.";
$langMailVerificationBody1 = "Thank you for registering at $siteName.<br><br>$langMailVerificationSuccess<br>$langMailVerificationClick<br>%s";
$langMailVerificationChangeBody = "To activate your account, your email address must be verified.<br><br>$langMailVerificationClick<br>%s";
$langMailVerificationSuccess3 = "Check that the email address below is correct and click on «<strong>$langMailVerificationNewCode</strong>»";
$langMailVerificationReq = "It is necessary to confirm your e-mail address before proceeding.<br /><br />$langMailVerificationSuccess3";
$langMailVerificationSuccess4 = "You will shortly receive an e-mail from the Management Team of the $siteName Asynchronous Distance Learning Platform, with the necessary instructions to complete your registration";
$langMailVerification = "Confirm Email $langUsersOf";

// mail_ver_settings.php
$langMailVerificationNotice = "If $langOfUserS e-mail confirmation is required
and confirmation is pending, then the following applies until it is complete
the confirmation:<br /><ul><li>the platform will refer the user to the e-mail confirmation page</li>
<li>the user will not receive e-mails with announcements from the $langsCourses he is attending</li></ul>";




/* * **********************************************************
 * restore_course.php
 * *********************************************************** */
$langInvalidArchive = "Invalid file $langsOfCourse";
$langRequest1 = "Click Browse to locate the $langsOfCourse backup you want to restore. Then click 'Send'.";
$langRequest2 = "If the backup copy, from which you will recover $langsCourse, is large in size and you cannot upload it, then you can type the exact path (path) where the file is located on the server.";
$langRestoreStep1 = "1° Retrieve $langsOfCourse from file or subdirectory.";
$langLesFound = "$langCourses found inside the file:";
$langLesFiles = "Her files $langsOfCourse:";
$langInvalidCode = "
Unacceptable code $langsOfCourse";
$langCopyFiles = "$langsOfCourse files copied.";
$langCourseExists = "There is already a $langsCourse with this code!";
$langWarning = "<em><font color='red'>WARNING!</font></em> If you choose not to add $langsOfCourse users and $langsOfCourse backup contains subsystems with information related to users (eg .eg 'User Tasks', 'File Exchange' or 'User Groups') then this information will <strong>NOT</strong> be retrieved.";
$langInfo1 = "The backup you sent contained the following information about $langsCourse.";
$langInfo2 = "You can change the $langsOfCourse code and whatever else you want (eg description, moderator, etc.)";
$langCourseType = "$langpre / $langpost";
$langUsersWillAdd = "$langsOfCourse users will be added";
$langErrorLang = "Problem! No languages for $langsCourse!";

/* * ***************************************************************
 * search.php
 * *************************************************************** */

$langTitle_Descr = "refers to its title or part of its title $langsOfCourse";
$langKeywords_Descr = "one or more keywords that identify its subject area $langsOfCourse";
$langInstructor_Descr = "the name or names of its coordinators $langsOfCourse";
$langCourseCode_Descr = "her code $langsOfCourse";
$langDescription_Descr = "refers to the description of $langsOfCourse, the subject modules and related resources";
$langAllFacultes = "In all $langsFaculties";

/* * **********************************************************
 * upgrade.php
 * ********************************************************** */

$langUpgSucNotice = "An error occurred, possibly some $langsCourse not working completely correctly.<br>In this case contact us at <a href='mailto:eclass@gunet.gr'>eclass@gunet.gr</a> describing the problem that occurred and sending (if possible) all messages that appeared on your screen.";
$langUpgCourse = "Upgrade $langsOfCourse";
$langUpgIndex = "Modify index.php file of $langsOfCourse";
$langUpgIndexingNotice = "Due to the large number of $langsOfCourses, search and indexing engines have been disabled. Please refer to the platform settings to restore them and follow the instructions for indexing.";
$langUpgNotIndex = "No change was made to her list $langsOfCourse";

/* * *****************************************************************
 * course_tools.php
 * ****************************************************************** */

$langAddCoursePage = 'Add a collaboration page';
$langCoursePages = 'Collaboration pages';


$langNoAdminAccess = '
        <strong>The page you are trying to access requires a username and password.</strong>
        <br>The platform automatically redirected you to the home page
        to log in before proceeding with other actions. Your session may have expired.';
$langLoginRequired = "
        <strong>You are not registered with $langsCourse and therefore cannot use the corresponding subsystem.</strong>
        <br>The platform automatically redirected you to the home page
        to enroll in $langsCourse, if enrollment is free.";
$langSessionIsLost = "
        <strong>Your session has expired.</strong>
        <br>The platform automatically redirected you to the home page
        to log in before proceeding with other actions.";
$langCheckProf = "
        <strong>The action you attempted to perform requires $langsOfCourse.</strong> administrator rights
        <br>The platform automatically redirected you to the home page
        to log in again.";
$langCheckCourseAdmin = "
        <strong>The action you attempted to perform requires $langsOfCourse.</strong> administrator rights
        <br>The platform automatically redirected you to the home page
        to log in again.";
$langLessonDoesNotExist = "
	<strong>Η $langsCourse that you tried to access does not exist.</strong>
    <br>This may be because you performed an illegal action or due to a technical problem
    on the platform.</p>";
$langCheckAdmin = "
        <strong>The action you tried to perform requires administrator rights.</strong>
        <br>The platform automatically redirected you to the home page
        to log in again.</p>";
$langCheckPowerUser = "
        <strong>The action you attempted to perform requires user admin rights and $langsOfCourses.</strong>
        <br>The platform automatically redirected you to the home page
        to log in again.";
$langCheckUserManageUser = "<strong>The action you attempted to perform requires user administrator rights.</strong>
<br>The platform automatically redirected you to the home page
to log in again.";
$langCheckDepartmentManageUser = "<strong>The action you attempted to perform requires section administrator privileges.</strong>
<br>The platform automatically redirected you to the home page
to log in again.";
$langCheckGuest = "
        <strong>The action you tried to perform is not possible with guest user privileges.</strong>
        <br>For security reasons the platform automatically redirected you to the home page
        to log in again.";
$langCheckMailVerify = "
        <strong>$langMailVerificationSuccess</strong>";
$langCheckPublicTools = "
        <strong>You tried to access a disabled tool $langsOfCourse.</strong>
        <br>For security reasons the platform automatically redirected you to the home page
        to log in again.";
$langWarnShibUser = "
        <strong>Warning:</strong> Because your authentication was done through Shibboleth you are not logged out of the platform!
        <br>To log out you should close your browser.</p>";
$langCheckUserRegistration = "<strong>The action you are trying to perform requires registration in $langsCourse</strong>";


/* * *************************************************************
 * unreguser.php
 * ************************************************************** */

$langExplain = "To unsubscribe from the platform, you must first unsubscribe from the $langsCourses you are enrolled in.";


//unregcours.php
$langCoursDelSuccess = "You have been successfully unsubscribed from $langsCourse";
$langConfirmUnregCours = "Are you sure you want to unsubscribe from $langsCourse?";

/* * *****************************************************************
 * usage.php
 * ****************************************************************** */

$langAllCourses = "All the $langsCourses";

$langAndTotalCourseVisits = "and total visits to $langsCourses";

$langFavouriteCourse = "Preference $langOfCourses";
$langFavouriteCourses = "Most popular $langCourses";

$langMonthlyCourseRegistrations = "User registrations on $langsCourse";
$langMonthlyCourseRegistration = "User registration at $langsCourse";


$langStatAccueil = "For the period requested, the following information is also available for all $langsOfCourse users:";
$langUsageCoursesHeader = "Total $langsOfCourses";
$langStatOfFaculty = "Statistics $langOfFaculty";

// for platform statistics
$langUsersCourse = "Users per $langsCourse";
$langVisitsCourseStats = "Page visits $langsOfCourses";
$langTotalVisitsCourses = "Total page views $langsOfCourses";


/* * ***********************************************
 * log.class.php
 * *********************************************** */

$langCourseActions = "Actions $langsOfCourse";

/* * **************************************************************
 * video.php
 * *************************************************************** */

$langOpenDelosReplaceInfo = "<p>If a selected link already exists in the media, it will be refreshed with the current content.</p><p><span style='color:red'>*</span> The link has already been added to its media $langsOfCourse.</p><p><span style='color:red'>**</span> The link has already been added to the media, but the OpenDelos platform has a newer version of it.</p>";




/* * ***********************************************************
 * listerqusers.php
 * ************************************************************ */
$langRequestStudent = "$langsOfStudent's application is closed!";


/* * ********************************************************************
  units.php
 * ********************************************************************* */

$langAddToCourseHome = "Display on its main page $langsOfCourse";
$langSeenToCourseHome = "It appears on its main page $langsOfCourse";
$langRemoveFromCourseHome = "Hide from its main page $langsOfCourse";
$langStudentViewEnable = "View page as $langsStudent";
$langStudentViewDisable = "View page as $langsTeacher";



/* * ********************************************************************
  mail_verify.php
 * ********************************************************************* */
$langMailNotVerified = "Your email is not confirmed. You will not be able to receive emails from $langsCourses until you confirm this.
To confirm it click";



/* * ******************************************
  Personal calendar
 * ***************************************** */
$langEventcourse = "Facts $langsOfCourses";
$langShowToAdminsandProfs = "managers and $langsTeachers";




/* * ******************************************
  Messages for Big Blue Button
 * ***************************************** */

$langBBBConf = "Its \"Telecollaboration\" settings $siteName";
$langBBBlockSettingsDisableCam = "Do not allow members to share a camera";
$langBBBwebcamsOnlyForModerator = "Show user cam only to moderators";
$langBBBlockSettingsDisableMic = "Do not allow members to use a microphone";
$langBBBlockSettingsDisablePrivateChat = "Disable private chats between members";
$langBBBlockSettingsDisablePublicChat = "Disable public chats by members";
$langBBBlockSettingsDisableNote = "Disable shared notes by members";
$langBBBlockSettingsHideUserList = "Hide user list to members (within BBB)";
$langNewBBBSessionPrivate = "<strong>only</strong> registered $langsOfCourse</strong> users participate";
$langBBBNotServerAvailableStudent = "Telecollaboration service is not possible. Contact the $langsTeacher of $langsOfCourse.";
$langBBBNotServerAvailableTeacher = "Telecollaboration is not enabled in the collaboration.";
$langBBBGetUsersError = "Unable to retrieve telecollaboration information ";
$langBBBSessionSuggestedUsers2 = "for more than 80, 50% of those registered in is proposed $langsCourse";
$langBBBMaxUsersJoinError = "The maximum number of telecollaboration participants has been reached. Please try logging in later or contact $langsOfTeacher at $langsOfCourse.";
$langBBBAddCourse = "Add collaboration";
$langBBBDeleteCourseSuccess = "The telecollaboration server has been disabled for the collaboration";
$langBBBAddCourseFail = "Unable to add, please select a valid partnership code";
$langBBBAddCourseFailExits = "The partnership already exists";
$langToAllCourses = "In all of them $langsCourses";
$langToAllCoursesInfo = "
Choose whether the server can be used by all $langsCourses or by some";
$langToSomeCourses = "In specific $langsCourses";
$langToSomeCoursesInfo = "The server will be usable by specific $langsCourses. To set which $langsCourses will use it go to \"Search $langsOfCourses\" and select \"Actions\".";
$langToNoCourses = "Σε καμία από τις $langsCourses";
$langBBBCronEnable = 'Note: You can enable automatic recording of presentations in telecollaboration without additional actions of moderators (opening a recording window). {Read more}';


/* * ***********************************************************
 * blog.php
 * ************************************************************ */

$langCourseBlog = "Blog $langsOfCourse";
$langBlogPermStudents = "$langsTeacher and $langsStudents have edit access";
$langBlogPermTeacher = "$langsTeacher only has edit permission";


/* * ***********************************************************
 * comments
 * ************************************************************ */
$langCourseCommenting = "Commenting on the $langsOfCourse home page";

/* * ***********************************************************
 * rating
 * ************************************************************ */
$langCourseRating = "Evaluation $langsOfCourse";
$langCourseAnonymousRating = "Rating $langsOfCourse by anonymous users";
$langRatingAnonDisCourse = "Rating by anonymous users is disabled for $langsCourses with non-free access without registration.";


/* * ***********************************************************
 * sharing
 * ************************************************************ */
$langCourseSharing = "Share $langsOfCourse page on social networks";
$langSharingDisCourse = "Social Sharing is disabled for $langsCourses with non-free access without registration.";



/* * ***********************************************************
 * Social Wall
 * ************************************************************ */
$langNoWallPosts = "There are no posts on the $langsOfCourse wall";




/**************************************************************
 * e-Portfolio
 * ************************************************************ */
$langePortfolioCollectionUserInfo = 'In the e-Portfolio resource collection you can add resources from the subsystems: blog (personal) and personal documents, if these are enabled.
The corresponding tabs appear only if resources from the relevant subsystems have been added to the collection.';






/* * ***********************************************************
 * Other
 * ************************************************************ */

$langMyCoursesSide = "My $langsCourses";
$langNumOpenCoursesBanner = "open<br>$langsCourses";
$langNumOpenCourseBanner = "open<br>$langsCourse";
$langNationalOpenCourses = "National Portal of Open Partnerships";





/* * ***********************************************************
 * Auto Judge
 * ************************************************************ */
//auto_judge.php
$langAutoJudge = "Auto reviewer settings of $siteName";
//antivirus.php
$langAntivirus = "Antivirus settings of $siteName";
//waf.php
$langWaf = "Web application firewall settings of $siteName";








// Messages for external tools
$langOpenDelosDescription = "<p>OpenDelos is the open software platform designed by GUNET for the Management, Recording and Broadcasting of Video Lectures.</p>
<p>The platform supports the action of <a href='http://ocw-project.gunet.gr/'>Open Academic $langOfCourses</a> in combination with <a href='http://openeclass. org/'>Open eClass platform</a> and the national search portal $langsOfCourses also developed by GUNET.</p>";
$langBBBDescription = "The open software <a href=\"https://www.bigbluebutton.org/\" target=\"_blank\">BigBlueButton</a> is a complete system of instant web-based communication and collaboration $langsOfTeachers and $langsOfStudents.</p>
<p>No installation of additional hardware (Software or Hardware) is required and it runs directly from the internet browser (Internet Explorer, Firefox, Chrome, Safari, etc.)r. To connect Open eClass with an installed BigBlueButton platform select <a href=\"bbbmoduleconf.php\">Settings</a>.</p>";
$langBBBLongDescription = "
Connect to the BigBlueButton video conferencing service.";
$langAutojudgeDescription = "<p>Automator is a tool that allows automatic correction of programming tasks. More specifically, through the tool the moderator can define scenarios that include input and output based on which the posted works are automatically graded.</p><p>This subsystem is connected to third-party compilation services that can be selected in the <a href =\"autojudgemoduleconf.php\">Settings</a>.</p>";

$langOpenMeetingsDescription = "<p>The <strong>Telecollaboration</strong> subsystem of the Open eClass platform is functionally supported by the <a href=\"http://openmeetings.apache.org/\" target=\"_blank\">Apache open software OpenMeetings</a> which is a complete system of direct web-based communication and collaboration $langsOfTeachers and $langsOfStudents.</p>
    <p>OpenMeetings belongs to the video conferencing / modern distance learning applications that do not require the installation of additional hardware (Software or Hardware). The application runs directly from the Internet browser (Internet Explorer, Firefox, Chrome, Safari, etc.) using Adobe Flash Player. To connect Open eClass with an installed OpenMeetings platform select <a href=\"openmeetingsconf.php\">Settings</a>.</p>";

$langLtiPublishShortDescription = "Publish $langsOfCourses via LTI protocol.";
$langLtiPublishLongDescription = "Publish $langsOfCourses via LTI protocol.";


// Messages for OpenMeetings
$langOpenMeetingsConf = "Settings \"OpenMeetings\" for $siteName";











/* * ******************************************
  Messages for collaboration platform
 * ***************************************** */
$langEclass = "Platform for Cooperation on Climate Change and Cultural Heritage";
$langEclassInfo = "
This platform was implemented in the framework of the project <Designing a National Strategy for the adaptation of cultural heritage monuments to the effects of climate change>. It provides an integrated environment of interaction between the cooperating agencies and experts in relation to the subject of the project.
";
$langCollaboration = "Cooperation";
$langSmCollaboration = "cooperation";
$langOfCollaboration = "Cooperation";
$langOfSmCollaboration = "cooperation";
$langCollaborationPlatform = "Collaboration platform";
$langEnableCollaboration = "Enable collaboration platform";
$langAlwaysEnabledCollaboration = "Always on";
$langCollaborationCreate = "Creating a partnership";
$langLogoutFromCollaboration = "Open Eclass platform";
$langCollaborationPlatformInfo = "The collaboration platform describes in detail the functionality and technical/technological
approach to the digital communication platform and the collaboration environment of
members of the various communities (executives of the cooperating agencies).</br> The tools it integrates ensure the required electronic
infrastructures to support online collaboration (e.g. forums, exchange
messaging, file sharing, voting, etc.) within the project.";
$langOpenCoursesTeacherConfirm = "Facilitator's Statement - Available Collaborative Materials Cover 100% of Course Material";
$langOpenCoursesTeacherConfirmVideo = "Moderator's Statement - Video Lectures Cover 80% of the Course Material";
$langCMeta['instructorGroup'] = "Coordinators";
$langTutorDefinition = "Moderator (right to supervise user groups)";
$langTheTeacher = 'The moderator';
$langCMeta['instructorGroup'] = "Moderators";
$langTiiSViewReports = "User access to the Similarity Report";
$langJustEdited = "You just updated the title collaboration ";
$langCreateCourseLeftForm = "The creation of cooperation is the most important action of the user – moderator on the platform.</br></br>
Enter a title for your collaboration.</br></br>
Enter a short description for the partnership.</br></br>
Select the Department in which the cooperation is offered or the cooperation category to which it belongs.</br></br>
Choose one of the available forms of partnerships. </br></br>
Specify, optionally, the distribution license of your collaboration.</br></br>
Specify the type of access to your collaboration. </br></br>
To complete the process of creating the collaboration, press the button with the indicator «Creating a Partnership».";
$langMonthlyReportInfo = "Aggregates (partnerships, users) of the last 12 months are presented.";
$langAddingDirectoryIndex = 'Add index.html files to collaboration subdirectories';
$langAdministratorGroup = 'Management of User Groups';
$m['WorkToAllUsers'] = "In all the $langsOfStudentss";
$m['WorkToUser'] = "Specific $langsOfStudentss";
$langOpenCoursesShort = "Open collaborations";


/* * ***********************************************************
 * Abuse Report
 * ************************************************************ */

 $langAbuseReportSaveSuccess = "Report sent to $langsOfCourse administrators successfully. They were updated by sending a message from the Message Exchange subsystem.";
 $langNoAbuseReports = "There are no abuse reports for this $langsCourse";





$langNewEclassVersion = 'New collaboration platform version';
$langNewEclassVersionInfo = 'A new version of the collaboration platform, %s,
she is available. Please visit the %s website for information
upgrade.';
/* * *********************************************************
 * install.php
 * ********************************************************* */
$langTitleInstall = "Collaboration Platform Installation Guide";
$langWelcomeWizard = "Welcome to the collaboration platform installation wizard!";
$langInstallBullet3 = "Write permissions to the directory where the collaboration platform is unzipped.";
$langCheckReq = "Checking prerequisite programs for the operation of the platform";
$langInfoLicence = "The collaboration platform is free application and distributed under the GNU General Public License (GPL). <br />
Please read the license and click 'Accept'";
$langMainDB = "Main Database of the platform";
$langLocalPath = "Path of the platform to the server";
$langWarnHelpDesk = "Attention: to the \"Email Helpdesk\" moderator requests are sent for an account on the platform";
$langErrorConfig = "<strong>An error occurred!</strong><br><br>Unable to create the config.php file.<br><br>Please check the access rights to the platform subdirectories and try the installation again.";
$langProtect = "Tip: To protect the platform, change file permissions
<tt>/config/config.php</tt> and <tt>/install/index.php</tt> and
allow read only (CHMOD 444).";
$langInstallSuccess = "Installation completed successfully! Click below to enter the collaboration platform";
$langEnterFirstTime = "Login to the platform";
$lang_max_glossary_terms = "Term definitions are not allowed to appear on $langsOfCourses pages if the total number of terms in the glossary is greater than:";
$lang_email_required = "$langOfUserS email must be required";
$lang_email_verification_required = "Confirmation of $langOfUserS e-mail is mandatory";
$lang_dont_mail_unverified_mails = "Do not send e-mails to $langUsersS who have not confirmed their e-mail address";
$lang_am_required = "$langsOfStudent registration number is mandatory when registering";
$lang_dropbox_allow_student_to_student = "Allow $langsOfCourse messaging between $langsOfStudents in the Messaging subsystem";
$lang_course_multidep = "Allow $langsCourses to belong to multiple $langsFaculties";
$lang_user_multidep = "Allow users to register in multiple $langsFaculties";
$lang_restrict_teacher_owndep = "
Don't allow $langsOfCourses to be created by $langsTeachers in classes they don't belong to";
$lang_allow_teacher_clone_course = "Allow $langsOfCourses to be cloned from $langsTeachers";
$lang_disable_log_course_actions = "Disable logging of user actions within $langsCourses";
$lang_disable_log_system_actions = "Disable logging of user actions outside of $langsOfCourses";
$langUnsubscribeCourse = "Disable collaboration opt-out";
$langDefaultInstitutionName = 'GUNet Academic Internet';
$lang_course_metadata = "Postcomment $langOfCourses";
/* * **********************************************************
 * upgrade.php
 * ********************************************************** */
$langUpgTooOld = 'The version of the platform you are upgrading from
it is very old. Please upgrade your installation first
to version 3.0 and then to the latest version.';
$langUpgReady = "You are now ready to use the new version of the platform!";
$langUpgradeStart = 'Start of platform upgrade';
$langDatabaseExists = 'Warning: Database "%s" already exists. The boards
contained in it will be deleted if they have the same names as those that
platform is using (either coincidentally, or if it is an existing
installation of the platform). Before proceeding, confirm that it does not exist
problem or go back to the previous step and enter another base name.';
$langDefaultThemeSettings = 'Collaboration Default';
$langLoginBanner = "Login Screen Platform Banner";
$langNextGeneration = "The project is implemented within the framework of the National Recovery and Resilience Plan Greece 2.0 with the funding of the European Union – NextGenerationEU";
$langCookieInfo = "This website uses cookies to ensure you get thge best experience on our website.";
$langCategoryDescription = "Collaboration description";
$langImplBodies = "Implementation Bodies";

/* * **********************************************************
 * manual.php
 * ********************************************************** */
$langDesPlatform = "Brief Description Platform for Cooperation on Climate Change and Cultural Heritage";
$langManualTeacher = "User with collaboration creation rights";
$langManualStudent = "Simple user";
$langCollaborationHome = "Electronic collaboration";
$langPasswordReset = "Password reset";
$langMenuChoicesOfUser = "User options menu";
$langManageCollaborations = "Partnership management";
$langManagePlatform = "Platform management";
$langMyCourses = "My Collaborations";
$langMetaTeacher = "User with creation rights";
$langBackupCourse = "Collaboration archive";
$langCode = "$langCourse code";


/* * **********************************************************
 * rename course word to collaboration
 * ********************************************************** */

$langOpenCoursesLicense = "Cooperation License";
$langAccess = "Cooperation Access Type:";
$langAvailableTypes = "Cooperation access";
$langEmailOption = "Send announcement (via email) to registered ".$langsStudents."";
$langTypeCollaboration = "Cooperation";
$langCourseKeywords = "$langCourse Keywords:";
$langKeywords_Descr = "one or more collaboration keywords";
$langPollNone = "There are no Questionnaires for the current collaboration.";
$langMetaTeacher = "Coordinator";
$langCourseVisits = "Collaboration Visits";
$langUsageVisits = "Collaboration Visits";



/* * **********************************************************
 * collaboration invitation
 * ********************************************************** */

 $langCourseInvitationHelp = 'If the option is active, partnership managers can invite external users to the platform to join their partnership.';
 $langCourseUsersInvitation = "Invite users to collaboration";
 $langcourseExternalUsersInviation = "Invite external users";
 $langCourseInviteOne = "User invitation";
 $langCourseInviteMany = "Invite users";
 $langDeleteInvitation = "Delete invitation";
 $langDeleteInvitationSuccess = "The invitation was deleted";
 $langCourseInvitationSubject = "Invitation to register on the platform";
 $langCourseInvitationBody1 = "You have received an invitation to register on the $siteName platform in order to participate in the partnership";
 $langCourseInvitationBody2 = "To proceed with registration, please follow the link below.";
 $langCourseInvitationSent = "The invitation has been sent!";
 $langCourseInvitationsSent = "Invitations were sent to";
 $langUserWithEmail = "The user with e-mail";
 $langAlreadyAccount = "already had an account on the platform and was added to your partnership.";
 $langAlreadyRegistered = "was already registered in your partnership.";
 $langAlreadyRegisteredUsers = "The following users already had an account on the platform and were added to the partnership";
 $langInvitationCustomEmail = "
 <p>Placeholder variables </p>
 <br>
 <ul><li>
  <li>[email] : E-mail address</li>
  <li>[link] : Registration link</li>
 </ul>";
 $langErrorInserting = "The following records of the file you uploaded had a problem. Either the e-mail is invalid or there were more fields than expected:";
 $langCourseInvitationUsersExcelInfo = "You can send a spreadsheet file (eg xls, xlsx, csv) with one or three columns.
 The first column must contain the e-mails of the users you wish to invite to the collaboration.
 The second and third columns can optionally contain the last name and first name of each user.
 Note that users who are already on the platform with the e-mail will be immediately added to the partnership without receiving an invitation and without further notification.";
 $langSendReminder = "Send a reminder";
 $langNoLongerValid = 'The link you followed is no longer valid.';
 $langInvitationAlreadyUsed = 'You have already registered on the platform through
 link you followed. You can now log in with your username
 your e-mail address and the password you had chosen.';
 $langRegisterAsVisitor = 'Register as a guest';
 $langCourseInvitation = 'Invitation to register for the partnership';
 $langCourseMetadata = "Metadata $langsOfCourse";

 $langInfoAboutCollabRegistration = "The partnership you selected is closed. To register, you must
 to complete the following application, which will be recorded and sent to the administrators of the collaboration.";
 $langLabelCollabUserRequest = "Application for collaboration registration";
 $langRequestReasonsCollab = "State the reasons you wish to register for the collaboration.";
 $m['password_collab'] = "Password for collaboration";
 $lang_openCourse_inModal = "Should the collaboration information be displayed in Modal (Frame) in the open collaborations?</br>
 If you do not select this option, the information of the partnership will be displayed in a new page of your browser.";
 $langGroupWorkIntro = '
	Assignments available in this collaboration are displayed below. Please select
	the assignment you wish to submit on behalf of your group and
	add any comments you want to be read by the collaboration consultant or coordinator. Please note that, when a submission has already been made by you or another member of your group, and you submit a new file for the same assignment, this file will replace the old file in the system (the old file will be deleted).
	Furthermore, no new submissions are allowed when the assignment has
	been graded.';
$langSessionCompletionActivated = "Session completion enabled";
$langInvalidCourseSessionPrerequisites = "Session completion is not enabled.";
$langManageSession = "Session management";
$langSessionCompletion = "End of session";
$langSessionCompletionMessage = "The session ended successfully";
$langNewSessionPrerequisiteFailInvalid = "Unable to add! Please select a valid session.";
$langSessionHasNotCompletionEnabled = "Unable to add! The session you selected does not have session completion enabled.";
$langNewSessionPrerequisiteFailAlreadyIn = "Unable to add, the session you selected is already in the list of prerequisites.";
$langSessionPrerequisites = "Prerequisite session";
$langNoSessionPrerequisite = "No prerequisite session has been set.";
$langSessionNotStarted = "The session has not started yet";
$langSessionNotCompleted = "The activities of the previous session have not been completed";
$langResourceBelongsToSessionPrereq = "The activity participates in session completion";
$langSessionResourseParticipatesInSessionCompletion = "The resource cannot be deleted as it participates as a session completion activity";
$langWithoutCompletedResource = "No activity submission";
$langSessionHasCompleted = "The session ended successfully";
$langCompletedSessionWithoutActivity = "End session without submitting activities";
$langSessionCompletedNotContinue = "Ending the session is not allowed.</br>
 Maybe there is an incomplete prerequisite session or there are activities that have not been completed";
$langCompletedSession = "Completed";
$langCompletedConsulting = "Complete counseling";
$langAddSessionConsultingCompleted = "Registration of counseling completion sessions is complete.";
$langCompletedConsultingInfo = "Sessions for which <strong>session completion</strong> is enabled are shown below. </br>
 Choose which of these will correspond to the completion of a user's consultation.</br>
 For the selected session, each user's completion percentage will be displayed in detail.";
$langCompletedSessions = "Completed session <i class='fa-solid fa-check fa-lg Success-200-cl ps-2'></i>";
$langNotCompletedSession = "Incomplete session";
$langResponsibleOfSession = "Registered Counselors - Session Manager";
$langSessionParticipants = "Registered beneficiaries/members - Participants";
$langContinueToCompletetionWithoutAct = "Do you want to proceed to complete the session without submitting any activities - resources?";
$langOfSubmitAssignment = "By submitting work";
$langOfSubmitDocument = "By submitting document";
$langSessionCompletedIsActivated = "Session completion without submitting activities is already enabled";
$langDocSender = "Users deliverable";
$langPreviousDocDeleted = "The previous document was deleted.";
$langWithTCComplited = "Upon completion of video conference";
$langTCComplited = "End of conference call";
$langContinueToCompletetionWithCompletedTC = "Do you want to proceed to end the session once the remote conference is finished?";
$langResourceAddedWithSuccess = "Resource added successfully.";
$langResourceExists = "The resource already exists.";
$langResourceNoExists = "The resource does not exist.";
$langDelAllSessions = "Delete all sessions";
$langContinueToDelAllSessions = "Do you want to proceed to delete all sessions?";
$langDelAllSessionSuccess = "Sessions deleted successfully.";
$langALLSessions = "All";
$langSubmitCompletion = "Submit completion";
$langContinueToUserAwarded = "This action is related to the completion of the user's deliverable and concerns the activity listed in the <<About>> column of the table.</br>
 Do you want to proceed with this action?";
$langDocCompletionSuccess = "The action completed successfully.";
$langNoSubmitCompletion = "Cancel completion";
$langContinueToNoSubmiCompletion = "You want to proceed to cancel checkout";
$langDocCompletionNoSuccess = "You have canceled the completion of the document.";
$langExistsTc = "The current video teleconference already exists.";
$langViewDeliverable = "View deliverable";
$langExistResourcesForCompletion = "There are resources to complete. Deactivation is not complete.";
$langExistsInCompletedPrerequisite = "There is a prerequisite session that has not been completed.";
$langInfoForUploadedDeliverable = "You can delete your deliverable as long as it has not been checked by the consultant.";
$langUsedCertRes = "The activity has already been used by a user.";
$langFileExistsWithSameName = "A file with the same name exists. The upload was not completed.";
$langPercentageCompletedConsultingByUser = "Session completion rate per user";
$langPercentageCompletedConsulting = "Session completion rate";
$langAddCompletionCriteria = "Add criteria";
$langUserDeliverable = "User deliverables";
$langTotalDeliverable = "Total deliverables";
$langInfoUploadExistedDeliverable = "If you send a file and your deliverable already exists, then the old one will be deleted and replaced with the new one.";
$langResourceDateCreated = "Created";
$langUploadOnBehalfOf = "Uploading a file on behalf of a user";
$langOnBehalfOfUser = "On behalf of the user";
$langResourceΝοBelongsToSessionPrereq = "The activity does <strong> NOT </strong> participate in session completion";
$langNotExistDeliverables = "There are no deliverables";
$langTypeOutComment = "Type your comment...";
$langAddCommentsSuccess = "Your comment has been successfully added.";
$langCommentsByConsultant = "Comments by consultant";
$langInsertPassage = "Add text";
$langSomeComments = "Update on your deliverable";
$langSendEmailWithComments = "Upon submission of your comments, an informative email will be sent to the user about them.";
$langNoExistsTClink = "Video conference link not found in session resources.";
$langNotFolders = "To complete the session by submitting a deliverable, compressed folders cannot be selected.</br>
 It will be ignored if any folder is selected as deliverable.";
 $langSessionAcceptance = "You have been selected to join the current session.
 To complete the process you should click on the link below.
 The link will redirect you to the session page after first logging into the platform.
 To proceed with the registration press submit.";
$langQuestionAcceptanceSession = "Do you accept your registration for this session?</br>
 The Advisor will be notified by email about the selection.";
$langUserHasAcceptedSession = "User session registration ACCEPTED:";
$langUserHasNotAcceptedSession = "The session registration was NOT accepted by the user:";
$langProcessCompleted = "You have successfully completed the process.";
$langAnnouncedExistingSession = "Selected users will be notified by mail to register for the session.";
$langNotExistUsers = "There are no users";
$langWithConsent = "User consent for session registration?";
$langInfoWithConsent = "The user will be automatically logged into the session if user consent is not selected.";
$langContinueToRegistrationSession = "You have chosen to subscribe to this session. Do you want to proceed?";
$langCompleteRegistration = "Your registration has been completed successfully! You have entered your session area.";
$langUserConsent = "About user consent";
$langInProgressRegistration = "Pending user registration...";
$langSubmitRegistration = "User registration";
$langNoSubmitRegistration = "Unsubscribe";
$langCancelSessionRegistration = "User registration is about to be cancelled. The user will be able to rejoin the session.
 The process of completing the session by that user will not be removed.
 To delete it permanently including its progress on completing the session, you can go to edit the session.</br></br>
 Do you want to continue?";
$langInfoAboutDelUser = "Removing a user will also delete their progress on completing the session.";
$langUserReferences = "User references";
$langUserHasCompleted = "Has completed";
$langNotUploadedDeliverable = "The user has not submitted any deliverables";
$langNoCommentsAvailable = "There are no comments";
$langTableCompletedConsulting = "User reports";
$langShowOnlySessionWithCompletionEnable = "The user table lists all sessions that have <strong>session completion</strong> enabled";
$langCourseIsNotCollaborative = "Unauthorized action. Collaboration is not in the form of a session";
$langSessionsTable = "Sessions table";
$langSummaryScheduledSessions = "Sessions reports";
$langNoSessionsExist = "There are no sessions";
$langSessionHasCompletionResources = "The session has completion resources";
$langSessionInProgress = "In progress";
$langNextSession = "Next scheduled session";
$langNoExistsNextSession = "There is not next scheduled session";
$langSessionsNotStarted = "They have not started yet";
$langSessionsHasExpired = "They have expired";
$langNoSessionInProgress =  "No session in progress";
$langWithMeetingCompletion = "Upon completion of the live meeting";
$langCompletedSessionWithMeeting = "Upon completion of the live meeting";
$langContinueToCompletetionWithMeeting = "The session will end with the completion criterion of the scheduled live meeting. </br>
 Do you want to proceed with this action?";
$langCompletedSessionMeeting = "Completion by live meeting";
$langInfoForbiddenAddPrereq = "The operation you attempted cannot be completed as the current session is logged out by all users.";
$langAllCompletedResources = "The session resources have been executed successfully by the user.";
$langShowReportUserTable = "User session reports";
$langShowReportUserCurrentSession = "Current session user report";
$langCompletionResources = "Completion resources";
$langCompletedResources = "Completed resources";
$langResourceAsActivity = "Session completion resources";
$langSessionCondition = "Condition";
$langUserHasCompletedCriteria = "The user has joined the required resources";
$langUserHasNotCompletedCriteria = "User has not joined all required resources";
$langUseOfAppInfo = "Choose which collaborations the app will be used in";
$langUseOfServiceInfo = "Choose in which partnerships the service will be used";
$langUsersHaveCompletedCriteria = "Participants have successfully participated in the wrap-up activities of the session";
$langUsersCompletedCriteriaInProgress = "The process of completion of the session by the participants is in progress";
$langHasParticipatedInTool = "He has participated in the resource";
$langHasNotParticipatedInTool = "He has not joined the resource";