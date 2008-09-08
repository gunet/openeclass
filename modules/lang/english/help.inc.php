<?
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


// help_forums.php

$langWindowClose="Close window";

$langHDefault='Help non available';
$langDefaultContent='<p>There is no help text available for the page you
are currently viewing.</p>';


$langHFor="Forums help";
$langForContent="<p>The forum is a written asynchronous discussion tool.
 Where email allows one-to-one dialogue, forums allow public or semi-public
 dialogue.</p><p>Technically speaking, the students need only their
 browser to use eClass forums.</P><p>To organise forums, click on
 'Administer'. Discussions are organised in sets and subsets as
 following:</p><p><b>Category > Forum > Topic > Answers</b></p>To structure
 your students discussions, it is necessary to organise catgories and
 forums beforehand, leaving the creation of topics and answers to them. By
 default, the eClass forum contains only the category 'Public', a sample
 forum ans a sample topic.</p><p>The first thing you should do is deleting
 the sample topic and modify the first forum's name. Then, you can
 create, in the 'public' category, other forums, by groups or by themes, to
 fit your learning scenario requirements.</p><p>Don't mix Categories and
 forums, and don't forget that an empty category (without forums) does not
 appear on the student view.</p><p>The description of a forum can be the
 list of its members, the definition of a goal, a task, a theme...</p>";



// help_home.php

$langHHome="Home Page Help";
$langHomeContent="<p>For more convenience, Open eClass tools contain default
entries.  There is a small example in every tool to help you grasp quickly how
it works. It is up to you to modify the example or to delete it.</p>

<p>For instance, here on the Home Page of your course website,there is a small
introduction text saying 'This is the introduction text of your course. To
replace it by your own text, click below on modify.' Click on modify, edit it
and Ok. It's that simple. Every tool has the same logic: add, delete, modify,
which is the logic of dynamic websites.</p>

<p>When you first create your website, most of the tools are active. Here
again, it is up to you to deactivate the ones you don't need. You just have to
click on 'deactivate'.  Then it goes down to the grey section of your homepage
an becomes invisible to your students.  However, you can reactivate it whenever
you want, making it visible to the students once more.</p> <p>You can add your
own pages to your Home Page. These pages must be HTML pages (which can be
created by any Word Processor or Web Composer). Use 'Upload page and link to
Homepage' to send your page to the server. The standard header of your website
will be automatically merged with your new document, so that you just need to
concentrate on the content. If you want to link from your Home towards
existaing websites or pages existing anywhere on the web (even inside your own
site), use 'Add link on Homepage' The pages you have added to the Home page can
deactivated then deleted, where the standard tools can be deactivated, but not
deleted.</p>

<p>Once your course website is ready, go to 'Modify course info' and decide
what level of confidentiality you want.  By default, your course is hidden
(because you work on it).</p>";


$langHInit="Start Page Help";
$langInitContent="<p>You are currently in the Open eClass platform's start page.
Type your username and password to login to the platform. In case you
have forgotten your login information, click on 'Forgot your password?' and
in the form that appears enter your e-mail address, in order to recover
your username and password.</p>
<p><b>New user registration</b></p>
<p>If you are a student you have to register, by clicking in 'User
Registration', and then you have to choose your desired courses.</p> 
<p><b>Professor acount request</b></p>
<p>If you are a professor, you also have to register by clicking in 'Professor account request'.
 After that you must fill a form with some personal informations: Name, surname, username, phone, e-mail and
 the department in which you belong. After filling the form, your request will be sent in the administrators
 of platform. They will create your account and send you an e-mail will all the details of your account.
 Using your username / password you will be able to login in platform. After entering the platform you have to
 click in 'Create course site'. After completing some details about your course, your course will be created
 and you will be directed in your newly created course home page.</p>";

$langInit_studentContent = $langInitContent;

$langHPortfolio="User portfolio";
$langClar2Content="<p>Here you can find all courses you are registered to
(as a student), as well as all the courses you have created (as a professor).
You can enter any one of them by clicking on its title.</p>

<p>Using the options of the side menu, you can register to additional
courses or unregister from your currently selected courses, change
your profile information (such as name, password and preferred language),
or find an aggregation page with the announcements and agenda of your
courses.</p>";

$langHcourse_home_stud='Main course page';
$langcourse_home_studContent='<p>You are currently in the on-line course\'s
main page. From the side menu options, you can enter any of the course
modules that have been activated by the course instructors.
</p>';

$langHcourse_home_prof='Main course page';
$langcourse_home_profContent='<p class="helptopic">You are currently
in the main page of your on-line course. From the side menu options,
you can enter any of the modules where you can add your course\'s
content. Active tools are visible to the visitors and students of
your course, while inactive tools are only visible and accessible to
you.</p>
<p class="helptopic">Using the administration tools you can
activate or deactivate course modules, review and administer registered
users, change various options (such as course title, access control, etc.),
and view usage statistics.</p>';


// help_document.php

$langHDoc="Help Documents";
$langDocContent="<p>The Documents tool is similar to the FileManager of
 your desktop computer.</p><p>You can upload files of any type (HTML, Word,
 Powerpoint, Excel, Acrobat, Flash, Quicktime, etc.). Your only concern
 must be that your students have the corresponding software to read them.
 Some file types can contain viruses, it is your responsibilty not to
 upload virus contaminated files. It is a worthwhile precaution to check documents with
 antivirus software before uploading them.</p>
<p>The documents are presented in alphabetical order.<br><b>Tip : </b>If
 you want to present them in a different order, numerate them: 01, 02,
 03...</p>
<p>You can :</p>
<h4>Upload a document</h4>
<ul>
  <li>Select the file on your computer by clicking in 'Upload file'.</li>
<li>In the following form use 'Browse' button to search for the file in your computer.</li>
  <li>Launch the upload with the 'Upload' button 
  </li>
  </ul>
  <h4>Rename a document (a directory)</h4>
    <ul>
        <li>
            Click on the icon 'Rename' (<img src='../../template/classic/img/edit.gif' width=10 height=10
 align=baseline>).
        </li>
        <li>
            Type the new name in the field (top left)
        </li>
        <li>
            Validate by clicking 'Rename' button.
        </li>
    </ul>
        <h4>
            Delete a document (or a directory)
        </h4>
        <ul>
            <li>
           Click on the icon 'Delete' (<img src='../../template/classic/img/delete.gif' width=10 height=10 align=baseline>)</li>
        </ul>
        <h4>
            Make a document (or directory) invisible to students
        </h4>
        <ul>
            <li>
                Click on the icon 'Visible/invisible' (<img src='../../template/classic/img/visible.gif' width=10 height=10 align=baseline>)  
            </li>
            <li>
                After that the document (or directory) still exists but it is not visible by students anymore.
            </li>
            <li>
           To make it invisible back again, click on the icon 'Visible/invisible' (<img src='../../template/classic/img/invisible.gif' width=14 height=10 align=baseline> 
            </li>
        </ul>
        <h4>
            Add or modify a comment to a document (or a directory)
        </h4>
        <ul>
            <li>
                Click on the icon 'Comment' *(<img src='../../template/classic/img/information.gif' width=10
 height=10 align=baseline>)</li>
            <li>
                Type new comment in the corresponding field (top right).
            </li>
        </ul>
        <hr>
        <p>
            You can organise your content through filing. For this:
        </p>
        <h4>
            <b>
                Create a directory
            </b>
        </h4>
        <ul>
            <li>
                Click on the link 'Create a directory'.
            </li>
            <li>
                Type the name of your new directory in the corresponding field (top left)
            </li>
            <li>
                Validate by clicking 'Create directory' button.
            </li>
        </ul>
        <h4>
            Move a document (or directory)
        </h4>
        <ul>
            <li>
                Click on the icon 'Move' (<img src='../../template/classic/img/move_doc.gif' width=10 height=10 align=basename>)
            </li>
            <li>
                Choose the directory into which you want to move the document (or directory) in the corresponding scrolling menu (top left) (note: the word 'root' means you cannot go upper than that level in the document tree of the server).
            </li>
            <li>
                Validate by clicking on 'Move' 
            </li>
        </ul>
</p>";




// Help_user.php

$langHUser="Help Users";
$langUserContent="<b>Roles</b><p>Roles have no computer related function.
 They do not give rights on operating the system.</p>
<hr>
<b>Admin rights</b>
<p>Admin rights, on the other hand, correspond to the technical
 authorisation to modify the content and organisation of the course
 website. For the moment, you can only choose between giving all the admin
 rights and giving none of them.</P>
<p>To allow an assistant, for instance, to co-admin the site, you need to
 register him in the course or be sure he is already registerd, then click
 on 'Add' under 'Administrator'.</p><hr>
<b>Add One user</b>
<p>To add a user for your course, fill the fields and click in 'Search' button. After finding user click in 'Register user to course' to register him/her in your course</p>";


// Help guest user
$langHGuest = "Add Guest Account";
$langGuestContent = "<p>By clicking in 'Add guest account' you can create an account for a guest user in a course. A guest user, can have access in the initial course page and of course the various active course tools but can't upload or modify anything.</p>";



// Help questionnaire.php
$langHQuestionnaire="Questionnaire Help";
$langQuestionnaireContent="<p>With this tool can create and manage questionnaires.</p>
<p>To create a questionnaire click on 'Create Questionnaire'. In the next form fill the title and the start / end date of the questionnaire. You can choose the question type by clicking in the corresponing buttons ('New mulitple choise question' and 'New text fill question'. After completing the questions and their answers click on 'Create Questionnaire' 
</p>
<p>Questionnaire results can accessed by the questionnaire admin page.</p>";


// Help exercise.php
$langHExercise="Help exercises";
$langExerciseContent="<p>The exercise tool allows you to create exercises that will contains as many questions as
you l
ike.<br><br>
There are various types of answers available for the creation of your questions :<br><br>
<ul>
  <li>Multiple choice (Unique answer)</li>
  <li>Multiple choice (multiple answers)</li>
  <li>Matching</li>
  <li>Fill in the blanks</li>
</ul>
An exercise gathers a certain number of questions under a common theme.</p>
<hr>
<b>Exercise creation</b>
<p>In order to create an exercise, click on the link &quot;New exercise&quot;.<br><br>
Type the exercise name, as well as an optional description of it.<br><br>
You can also choose between 2 exercise types :<br><br>
<ul>
  <li>Questions on an unique page</li>
  <li>One question per page (sequential)</li>
</ul>
and tell if you want or not questions to be randomly sorted at the time of the exercise running.<br><br>
Then, save your exercise. You will go to to the question administration for this exercise.</p>
<hr>
<b>Question adding</b>
<p>You can now add a question into the exercise previously created. The description is optional, as well as the
picture
 that you have the possibility of linking to your question.</p>
<hr>
<b>Multiple choice</b>
<p>This is the famous MAQ (multiple answer question) / MCQ (multiple choice question).<br><br>
In order to create a MAQ / MCQ :<br><br>
<ul>
  <li>Define answers for your question. You can add or delete an answer by clicking on the right button</li>
  <li>Check via the left box the correct answer(s)</li>
  <li>Add an optional comment. This comment won't be seen by the student till this one has replied to the
question</li>
  <li>Give a weighting to each answer. The weighting can be any positive or negatif integer, or zero</li>
  <li>Save your answers</li>
</ul></p>
<hr>
<b>Fill in the blanks</b>

<p>This allows you to create a text with gaps. The aim is to let student find words that you have removed from
the text.<br><br>
To remove a word from the text, and so to create a blank, put this word between brackets [like this].<br><br>
Once the text has been typed and blanks defined, you can add a comment that will be seen by the student when it
replies to the question.<br><br>
Save your text, and you will enter the next step that will allow you to give a weighting to each blank. For
example, if the question worths 10 points and you have 5 blanks, you can give a weighting of 2 points to each
blank.</p>
<hr>
<b>Matching</b>
<p>This answer type can be chosen so as to create a question where the student will have to connect elements from
an unit U1 with elements from an unit U2.<br><br>
It can also be used to ask students to sort elements in a certain order.<br><br>
First define the options among which the student will be able to choose the good answer. Then, define the
questions which will have to be linked to one of the options previously defined. Finally, connect via the drop-down menu
elements from the first unit with those of the second one.<br><br>
Notice: Several elements from the first unit can point to the same element in the second unit.<br><br>
Give a weighting to each correct matching, and save your answer.</p>
<hr>
<b>Exercise modification</b>
<p>In order to modify an exercise, the principle is the same as for the creation. Just click on the picture <img
src=\"
../../template/classic/img/edit.gif\" border=\"0\" align=\"absmiddle\"> beside the exercise to modify, and follow instructions
above.</p>
<hr>
<b>Exercise deleting</b>
<p>In order to delete an exercise, click on the picture <img src=\"../../template/classic/img/delete.gif\" border=\"0\"
align=\"absmiddle\"> beside the exercise to delete.</p>
<hr>
<b>Exercise enabling</b>
<p>So as for an exercise to be used, you have to enable it by clicking on the picture
<img src=\"../../template/classic/img/invisible.gif\" border=\"0\" align=\"absmiddle\"> beside the exercise to enable.</p>
<hr>
<b>Exercise running</b>
<p>You can test your exercise by clicking on its name in the exercise list.</p>
<hr>
<b>Random exercises</b>

<p>At the time of an exercise creation / modification, you can tell if you want questions to be drawn in a random
order among all questions of the exercise.<br><br>
That means that, by enabling this option, questions will be drawn in a different order each time students will
run the exercise.<br><br>
If you have got a big number of questions, you can also choose to randomly draw only X questions among all
questions available in that exercise.</p>
<hr>
<b>Question pool</b>
<p>When you delete an exercise, questions of its own are not removed from the data base, and can be reused into a
new exercise, via the question pool.<br><br>
The question pool also allows to reuse a same questions into several exercises.<br><br>
By default, all questions of your course are shown. You can show the questions related to an exercise, by chosing
this one in the drop-down menu &quot;Filter&quot;.<br><br>
Orphan questions are questions that don't belong to any exercise.</p>";


// help work

$langHWork = "Work";
$langWorkContent = "
<p>Work tool is a complete tool for creating / submittion of assignments.</p>
<p>As a professor, you can create an assignment by clicking on <b>\"New Assignment\"</b>.
Fill in the title of the assignment, define a deadline and optionally add a comment.</p>
<p>When the assignment has completed, do not forget to activate it by clicking on the icon
<img src=\"../../template/classic/img/invisible.gif\" border=\"0\" align=\"absmiddle\">. The assignment will be visible
and accessible by the students only when it is activated.
You can edit the assignment by clicking on the icon <img src=\"../../template/classic/img/edit.gif\" border=\"0\" align=\"middle\">
or deleting it by clicking on the icon <img src=\"../../template/classic/img/delete.gif\" border=\"0\" align=\"middle\">.
Clicking on the title of the assignment, you have access to the students submissions.
The corresponding details are the submission date and the filename.
Clicking on \"Download assignments (in .zip format)\" you will download all the submitted files by the students
in zip format for the corresponding assignment.
If you want to score the assignment, just fill the grade next to student name and click on the button
<b>\"Assignment grade\"</b>. The student will have his grade after clicking the assignment</p>

<p>On the other hand, student can have access to all visible assigments by the professor
The list of the assignments include the deadline, the professor grade and a tick mark denoting if the student has
uploaded an assignment or no.
Note, the student cannot upload an assignment after the deadline.
Also, if he / she has uploaded an assignment and wants to upload a new one, the old one will be replaced by the new
one.
</p>
";


// Help Group
$langHGroup="Help groups";
$langGroupContent="<p><b>Introduction</b></p>
<p>This tool allows to create and manage work groups.
At creation (Create groups), groups are emtpy. There are
many ways to fill them:
<ul><li>automatically ('Fill groups'),</li>
<li>manually ('Edit'),</li>
<li>self-registration by students (Groups settings: 'Self registration allowed...').</li>
</ul>
<p>These three ways can be combined. You can, for instance, ask students to self-register first.
Then discover that some of them didn't and decide then to fill groups automatically in
order to complete them. You can also edit each group to compose membership one student
at a time after or before self-registration and/or automatical filling.</p>
<p>Groups filling, whether automatical or manual, works only if there are already students
registered in the course (don't mix registration to the course with registration into groups).
Students list is visible in <b>Users</b> tool. </p><hr noshade size=1>
<p><b>Create groups</b></p>
<p>To create new groups, click on 'Create new group(s)' and determine number of groups to
create. Maximum number of members is optional but we suggest to chose one. If you leave max. field
unchanged, groups size maximum will be infinite.</p><hr noshade size=1>
<p><b>Group settings</b></p>
<p>You can determine Group settings globally (for all groups).
<b>Students are allowed to self-register in groups</b>:
<p>You create empty groups, students self-register.
If you have defined a maximum number, full groups do not accept new members.
This method is good for teachers who do not know students list when
creating groups.</p>
<b>Outils</b>:</p>
<p>Every group possesses either a forum (private or public) or a Documents area
(a shared file manager) or (most frequent) both.</p>
<hr noshade size=1>
<p><b>Manual edit</b></p>
<p>Once groups created (Create groups), you see at bottom of page, a list of groups
with a series of informations and functions
<ul><li><b>Edit</b> to modify manually Group name, description, tutor,
members list.</li>
<li><b>Delete</b> deletes a group.</li></ul>
<hr noshade size=1>";

// Help Agenda
$langHAgenda = "Agenda";
$langAgendaContent = "<p>You can add an event in agenda, by clicking on the link 'Add an event'. In the following form, choose a date, type a title and describe the details. After that press button 'Add / / Modify' An event will be created.</p>
<p>If you wish, you can change some event properties by clicking the icon 'Modify' or deleting an event by clicking
the icon 'Delete'.</p>";

// Help link
$langHLink = "Link";
$langLinkContent="<p>With the Links tool you can create links to various web pages.</p>
<p>To add a link click on the link 'Add link'. Type the url, the link name and (optionally) a small description. If you want you can choose the category which the link will belongs. Click on the 'Add' button to add the link.</p>
<p>You can organize your links by grouping them in categories. You can add a category by clicking in 'Add category' Type the category name and category description. After that click on the button 'Add'.</p> 
<p>Notice that you can edit every link to re-assign it into a new category (of course you need to create this category first).</p>";

// Help announcements
$langHAnnounce = "Announcements";
$langAnnounceContent = "<p>You can add announcements in a course by clicking in the link 'Add announcement'. In the following form type the title and the body of the announcement. After that click on the 'Add' button.</p><p>
Also, you can change the announcement clicking on the icon 'Modify' or delete an announcement clicking on the icon 'Delete'. If you want to mail your announcement to the registered students in your lesson, just check 'Send announcement (via email) to registered students'</p>";

// Help profile
$langHProfile = "My profile";
$langProfileContent = "<p>You can modify your personal info in the platform</p>
<li>You can modify your name, surname, and your e-mail address.</li>
<li>Also if you desire, you can modify your username and your password.</li>
<li>After making any changes, just click on the button 'Modify'.</li>";


//Help import
$langHImport = "Upload page";
$langImportContent = "<p>You can add your personal pages in course home page.
The page must be in HTML format and can be created with a word processor or
an editor for creating Web sites and pages. If you want to add a page upload the file .html
by pressing the button 'Browse', type the title of the page and press button 'Add'.
Your page will be linked from Homepage. If you want to send non HTML documents
(PDF, Word, Power Point, Video, etc.) use 'Documents tool'
These links can be deactivated and deleted.</p>";

$langHModule = "Add link in home page";
$langModuleContent = "<p>If you want to add links in lesson home page, just type the title and the address of the
link and press button 'Add'. These links can be deactivated and deleted.</p>";

//Help import page
$langHImport = "Upload html file";
$langImportContent = "<p>If needed, you can upload a file relative to your course. This file will be stored on the server. A link towards this file will be added on the left side menu, with the rest lesson tools.
The link will open in a new browser window.</p>
<p>To upload your html page click on 'Browse', choose the file you wish to upload, type a title in the 'Page Title' field and click on 'Add'.</p>
<p>The link of this file can be deactivated and deleted from the 'Tools administration' module.</p>";

//Help Course tools
$langHcourseTools = "Tools Management";
$langcourseToolsContent = "<p>This module is used to activate or deactivate lesson tools. Each tool's status is presented on one of the two columns and can be active or not.</p>

<p>In order to change the status of a tool click on the tools name to it and then click on '>>' to change it's status. You can move multiple tools by using CTRL+click. Finally, click  on 'Save changes' at the end of the table to save your changes.</p>
";

$langHInfocours = "Modify course info";
$langInfocoursContent = "<p>When the page of your course is ready, you can modify
course information. You can modify professor name, configure the users access rights
and modify the language that will be valid for every visitor of your course's website.
By the time you have completed the modifications press button 'Change'.
</p>
<p><u>Course categories:</u></p>
<p><b>Open courses :</b> are accessible from the public without any login/password
user authentication procedure.</p>
<p><b>Registration based accessed courses :</b> are accessible to users that
must be registered.</p>
<p><b>Closed courses :</b> are accessible to users already registered at eClass platform and
have permission from the professor to attend the specific course.</p>
<p><u>More actions:</u></p>
<p><b>Archive this course:</b> You can create a backup file for the course and then
download and save it to your computer. In case of unmeant deletion or destruction of the course
you can use the backup file but you have to contact with the administrator of the platform.</p>
<p><b>Delete the whole course website :</b> Deleting the course website will permanently delete all
the documents it contains and unregister all its students (not remove them from other courses).</p>
<p><b>Delete users from course :</b> You can delete all the users registered at your course
without removing them from other courses .</p>";

$langHConference = "Conference";
$langConferenceContent = "
<b><u>Description</u></b>
<p>This module gives the instructor the ability to conduct live meetings with the students. It offers 4 functionalities: <br></p>
<p>It offers 4 functionalities:</p>
<ul>
       <li>Video Conferencing (must first be installed by the administrator of the platform)</li>
       <li>Video Streaming</li>
       <li>Presentation and URL sharing</li>
       <li>Chat</li>
</ul>
<b><u>Video Conferencing</u></b>
<p><b><u>Instructor Options</u></b></p>
<p>
Regarding the video conferencing functionality, the instructor may select this option on entrance to the subsystem and automatically all participants who have entered the same subsystem are given the ability to connect. Each course on the eClass platform automatically utilizes a separate \"virtual conferencing room\" for all its participants. Technically, the instructor does not have speech control over the students, so he must make sure he establishes the rules of conduct for participation in the video conferences. Best practices include: a) the suggestion of keeping muted microphones for all participants until the need to speak arises, so that inadvertent noise will not be transmitted to other participants, and b) organizing test conferences before the needed date and time of an actual conference, so that all participants may familiarize themselves with the conferencing environment and equipment.
The equipment requirements for conferencing are just a set of speakers and a microphone (a camera is optional), while software requirements are the exclusive use of Microsoft Internet Explorer, along with NetMeeting version 3.0.1 on the personal computer of the participants. NetMeeting comes preinstalled in WinXP, while there is an installer available for Win2000, Win98, WinNT etc. Default settings on NM will work fine with this subsystem, but if NM \"Advanced Calling\" options have been configured before, users will need to disable Gatekeeper Use. To avoid last minute problems, it is best to require that all participants go through the \"Audio Tuning Wizard\" steps before the scheduled conference time. This option which is found on the \"Tools\" menu of NM will offer the participants a simple way to confirm the reliability of the speakers (to play back conference audio) and of the local microphone (to send local audio to the conference).
</p>
<p><b><u>Student Options</u></b></p>
<p>
Regarding the video conferencing functionality, the student will be given the ability to connect to the conference throught the web pages of this subsystem, once the instructor has activated this option. Each course on the eClass platform automatically utilizes a separate \"virtual conferencing room\" for all its participants. A good practice is for participants to keep all microphones muted, until the need to speak arises, so that inadvertent noise will not be transmitted to other participants. The use of headphones with integrated microphones is the best option for avoiding echo problems during the conferences.
</p>

<b><u>Video</u></b>
<p><b><u>Instructor Options</u></b></p>
<p>When the instructor selects the \"video\" option, a field is activated allowing the placement of a URL for the streaming video. After completion of the URL and the selection of \"Play\", video is loaded in the window above the controls with the embedded use of the appropriate media player, as set in the OS to handle the correct file extensions. The instructor must have informed the students ahead of time that they will need to have installed the media player that the material he presents will require.</p>
<p><b><u>Student Options</u></b></p>
<p>The students do not need to make any selections. The video material will load automatically on their browsers (assuming the correct media player is installed) as soon as the instructor selects the video streaming option.</p>


<b><u>Presentation </u></b>
<p><b><u>Instructor Options</u></b></p>
<p>The instructor uses the field \"Presentation URL\" to specify the web page (URL) that he wishes to distribute to all the student browsers and selects \"OK\". The web page is then loaded on his presentation window on the right. The same web page get loaded in the student presentation window on his browsers. Note that once the initial URL is loaded, the instructor's view of the loaded page is not synchronized with that of the student's. E.g. if the instructor clicks on any link on that page, he will have to instruct the students to do the same on their browser manually. Only web pages that are loaded through the \"Presentation URL\" field are synched to the student browsers. </p>
<b><u>Student Options</u></b>
<p>The student is presented with the web page that the instructor has placed in his browser. From then on, he must follow the instructor's oral guidance in order to browse through the same links from this web page on.</p>

<b><u>Chat</u></b>
<p><b><u>Instructor Options</u></b></p>
<p>The instructor has the ability to exchange messages with the students that are using the Conference Module by typing the message on text field at the bottom of the page and then pressing \">>\". The instructor also has the option to clear all past messages from appearing on the page by selecting \"Clear\". </p>
<p><b><u>Student Options</u></b></p>
<p>The instructor has the ability to exchange messages with the students that are using the Conference Module by typing the message on text field at the bottom of the page and then pressing \">>\". The instructor also has the option to clear all past messages from appearing on the page by selecting \"Clear\".</p>
";

$langHVideo = "Video";
$langVideoContent ="
<p>Courses might contain audio and video files as contend. Audio and video files can be distributed either via downloading or streaming. When distributed via downloading there is a significant amount of time for waiting until downloading finishes in order to playback the file. When distributed via streaming there is no wait time and playback starts immediately. Video module adds streaming capabilities to eClass.</p>
<p><b><u>Instructor Options</u></b></p>
<p>
You can upload video file in several formats like mpeg, avi etc. Choose \"Add video\" and type the path to the video file you want or click on \"Browse\" to locate it visually. Optionally you can fill in the \"Document title\" & \"Description\" fields. Click on \"Add\" in order to upload the file to the platform. Additionally you can add video links to your courses. Choose \"Add video link\" and then type the link to the file on the streaming server you want to add in the \"URL\" field. Optionally you can fill in the \"Document title\" & \"Description\" fields. When finished click on \"Add\". Once added you can modify any of the fields by choosing \"Modify\" or you can delete a file or link by choosing \"Delete\". By choosing \"Delete whole list\" you can remove all the files and links added to a course. If there is a streaming server integrated with the platform the process of video files addition is transparent. There is no extra care or action to be done in order the files to be streamed from the streaming server. Keep in mind that video files and links will be available all the time if someone uses the direct url to them.
</p>
<p><b><u>Student Options</u></b></p>
<p>
Choose the video file you want to playback. If there is a streaming server integrated with eClass, the student should be informed what client should use for playback.
</p>
";

$langHCoursedescription = "Course Description";
$langCoursedescriptionContent = "<p>You can add some additional info about the course, when you click in 'Create
and Edit'. You can add a category, selecting it from the drop down box and click in the button 'Add'.
After entering the information you want press the button 'Add'. </p><p>If, for some reason, you decide that don't want to enter
the information press 'Return and Cancel'. Whenever you want, you can modify the information you have entered by
clicking 'Modify' or deleting it by clicking on 'Delete'.</p>";

$langHModule = "Add link in home page";
$langModuleContent = "<p>If you want to add links in course home page, just type the title and the address of the
link and press button 'Add'. These links can be deactivated and deleted.</p>";


// Help  Scorm - Learning Path
$langHPath="Help - Learning Path";
$langPathContent="
The Learning Path tool has four functions:
<ul>
<li>Create a learning Path</li>
<li>Import a Scorm or IMS format Learning path</li>
<li>Export a Scorm 2004 or 1.2 compliant Learning path</li>
<li>Track the progress of the students following the Learning paths</li>
</ul>

<p><b>What is a Learning Path ?</b></p>

<p>A Learning Path is a sequence of learning steps included in modules. It can be
content-based (looking like a table of contents) or activities-based, looking like
an agenda or a programme of what you need to do in order to understand and practice a
certain knowledge or know-how.</p>

<p>In addition to being structured, a learning path can
also be sequenced. This means that some steps will constitute pre-requisites for the steps
after them (\"you cannot go to step 2 before step 1\"). Your sequence can only be suggestive
(you show steps one after the other).</p>

<p><b>How to create our own Learning Path ?</b></p>

<p>The first step is to arrive to Learning Path List section. In
the Learning Path List screen, there is a link to it. There you can create
many paths by clicking onto <i>Create a new learning path</i>. But they are
empty, till you add modules and steps to them.</p>

<p><b>What are the steps for these paths ? (What are the items that can be added ?)</b></p>

<p>Some of the Eclass tools, activities and contents that you consider to be useful
and connected to your imagined path can be added:</p>

<ul>
<li>Separate documents (texts, pictures, Office docs, ...)</li>
<li>Labels</li>
<li>Links</li>
<li>Eclass Tests</li>
<li>Eclass Course Description</li>
</ul>

<p><b>Other features of Learning Path</b></p>

<p>Students can be asked to follow (read) your path in a given order. This means
that for example students cannot go to Quiz 2 till they have read Document 1.
All items have a status: completed or incomplete, so the progress of students is
clearly available through the <i>Tracking</i> tool.</p>

<p>If you alter the original title of a step, the new title will appear in
the path, but the original title will not be deleted. So if you want
test8.doc to appear as 'Final Exam' in the path, you do not have to rename
the file, you can use the new title in the path. It is also useful
to give new titles to links as they are too long.</p>
<br>


<p><b>What is a Scorm or IMS format Learning path and how to upload (import) it ?</b></p>

<p>The learning path tool allows you to upload SCORM and IMS compliant course
contents.</p>

<p>SCORM (<i>Sharable Content Object Reference Model</i>) is a public standard
followed by major e-Learning actors like NETg, Macromedia, Microsoft, Skillsoft,
etc. and acting at three levels:</p>

<ul>
<li><b>Economy</b>: Scorm allows whole courses or small content
units to be reusable on different Learning Management Systems (LMS)
through the separation of content and context,</li>
<li><b>Pedagogy</b>: Scorm integrates the notion of
pre-requisite or <i>sequencing</i> (<i>e.g. </i>\"You
cannot go to chapter 2 before passing Quiz 1\"),</li>
<li><b>Technology</b>: Scorm generates a table of contents as
an abstraction layer situated outside content and outside the LMS. It
helps content and LMS communicate with each
other. What is communicated is mainly <i>bookmarks</i> (\"Where is John in the
course ?\"), <i>scoring</i> (\"How did John pass the test ?\") and <i>time</i> (\"How much
time did John spent in chapter 1 ?\").</li>
</ul>

<p><b>How to create a SCORM compliant learning path ?</b></p>

<p>The most natural way is to use the Eclass Learning Path Builder. However, you may want to
create complete Scorm compliant websites locally on your own computer before uploading it
onto your eclass platform. In this case, we recommend the use of a sophisticated tool like
Lectora&reg; or Reload&reg;</p>

<p><b>Useful links</b></p>

<ul>
<li>Adlnet: authority responsible for Scorm normalisation, <a
href=\"http://www.adlnet.org/\">http://www.adlnet.org</a></li>
<li>Reload: Open Source free Scorm player and editor, <a
href=\"http://www.reload.ac.uk/\">http://www.reload.ac.uk</a></li>
<li>Lectora: Scorm publisher authoring software, <a
href=\"http://www.trivantis.com/\">http://www.trivantis.com</a></li>
</ul>

<p><b>Note:</b></p>

<p>The Learning Path section lists all the <i>self-built Learning Paths</i>
and all uploaded <i>Scorm format Learning Paths</i>, as well.</p>
";

$langHUsage = "Usage Statistics";
$langUsageContent = "<p>The usage statistics modules allows the professor to see statistics about his lesson. They are presented in the form of charts or lists.</p>
<p><strong>Statistics categories</strong></p>
<ul>
<li>Usage statistics</li>
<li>Favorite modules</li>
<li>User visits</li>
<li>Old statistics</li>
</ul>
<p>Usage statistics can be grouped by number of visits or visit duration. Additionally, the professor is able to choose the modules he wants to see and the timeframe.</p>
<p>The favorite modules statistics can be grouped by number of visits or visit duration. Additionally, the professor is able to choose the modules he wants to see.</p>
<p>The user visits statistics can be grouped by users, either all, or by letter.</p>
<p>Old statistics can be grouped by number of visits or visit duration. Additionally, the professor is able to choose the modules he wants to see and the timeframe.</p>
";

//Help Create Course Wizard
$langHCreateCourse = "Create Course Wizard";
$langCreateCourseContent = "<p>The Create Course Wizard is one of the most important tools of the platform. By using this, the user-instructor is able to create new courses in the platform and configure them.</p><p>The wizards consists of 3 steps. Filling in every field with an asterisk, is mandatory. Under some fields, lie exemplary information to help the user with the filling-in.</p><p>In case the user enters invalid data in a field, the system informs the user and prompts him to correct the error so as to be able to continue to the next step.</p>";


// Wiki Help
$langHWiki = "Help - Wiki";
$langWikiContent = "<p>To create a new Wiki</p>
<ul>
<li><b>Title of the Wiki</b>: choose a title for the Wiki</li>
<li><b>Description of the Wiki</b>: choose a description for the Wiki</li>
<li><b>Access control management</b>: set the access ontrol of the Wiki by checking/uncheking the box (see below)</li>
</ul>
<p>To enter a Wiki click on the title of the Wiki in the list.</p>
<p>To change the properties of a Wiki click on the icon <img src='../../template/classic/img/edit.gif' align='absmiddle' border='0'>.</p> 
<p>To delete a Wiki click on the icon <img src='../../template/classic/img/delete.gif' align='absmiddle' border='0'></p> 
<p>To get the list of the last modified pages click on the link 'Recent changes'.</p>";