<?
/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

 /***************************************************************************
 *                           lang_korean.php  -  description
 *				ver : kor_0.1.24
 *
 *	허접 번역:노윤탁 (참고:최의종 님의 홈페이지,도움:신지현 님)
 *	email : geogo@hitel.net
 *
 *	음..아직 완전히 번역하지 못한 부분이 많이 있습니다.
 *	혹 틀린 부분이 있거나 자연스럽지 못한부분이 있으면(아마..많을것 같은..)
 *	메일주십시요..즉시 수정해 놓겠습니다.
 *	아직 번역되지 않은 부분도 변역해서 메일로 보내주시면 즉시 수정해서 올리겠습니다.
 *	이 파일을 lang_korean.php 으로 저장하여 ./phpBB/language 디렉토리에 넣어주시면
 *	됩니다.
 ***************************************************************************/

$l_special_meta = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=EUC-KR\">";

$l_forum 	= "포럼";
$l_forums	= "포럼";
$l_topic	= "주제";
$l_topics 	= "주제";
$l_replies	= "답변";
$l_poster	= "글쓴이";
$l_author	= "글쓴이";
$l_views	= "조회";
$l_post 	= "게시물";
$l_posts 	= "게시물";
$l_message	= "쪽지";
$l_messages	= "쪽지";
$l_subject	= "제목";
$l_body		= "$l_message 본문";
$l_from		= "From";   // Message from
$l_moderator 	= "관리자";
$l_username 	= "아이디";
$l_password 	= "암호";
$l_email 	= "전자우편";
$l_emailaddress	= "전자우편 주소";
$l_preferences	= "설정";

$l_anonymous	= "익명";  // Post
$l_guest	= "손님"; // Whosonline
$l_noposts	= "No $l_posts";
$l_joined	= "가입날짜";
$l_gotopage	= "페이지 이동";
$l_nextpage 	= "다음 페이지";
$l_prevpage     = "이전 페이지";
$l_go		= "가기";
$l_selectforum	= "$l_forum 을 선택하세요";

$l_date		= "날짜";
$l_number	= "번호";
$l_name		= "이름";
$l_options 	= "옵션";
$l_submit	= "확인";
$l_confirm 	= "Confirm";
$l_enter 	= "Enter";
$l_by		= "by"; // Posted by
$l_ondate	= "on"; // This message is edited by: $username on $date
$l_new          = "New";

$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "캐릭터";
$l_on		= "On";
$l_off		= "Off";
$l_yes		= "Yes";
$l_no		= "No";

$l_click 	= "Click";
$l_here 	= "here";
$l_toreturn	= "돌아가기";
$l_returnindex	= "포럼 목록으로 $l_toreturn ";
$l_returntopic	= "글 목록 으로 $l_toreturn";

$l_error	= "Error";
$l_tryagain	= "돌아가서 다시 시도해 보십시요.";
$l_mismatch 	= "암호입력이 잘못되었습니다.";
$l_userremoved 	= "입력하신 사용자는 아이디가 삭제되었습니다.";
$l_wrongpass	= "압호입력이 잘못되었습니다.";
$l_userpass	= "아이디와 암호를 입력해주세요.";
$l_banned 	= "이 포럼으로 부터 거절당하였습니다. 이의있으시면 포럼관리자에게 연락주십시요.";
$l_enterpassword= "암호를 입력하세요.";

$l_nopost	= "이 포럼에 글을 게시할 권한이 없습니다.";
$l_noread	= "이 포럼의 글을 읽을 권한이 없습니다.";

$l_lastpost 	= "마지막 $l_post";
$l_sincelast	= "마지막 접속 이후";
$l_newposts 	= "$l_sincelast 새 $l_posts 있음";
$l_nonewposts 	= "$l_sincelast 새 $l_posts 없음";

// Index page
$l_indextitle	= "포럼 처음";

// Members and profile
$l_profile	= "개인정보";
$l_register	= "회원가입";
$l_onlyreq 	= "비밀번호를 변경할때에는 이곳에도 써 주세요.";
$l_location 	= "소속";
$l_viewpostuser	= "View posts by this user";
$l_perday       = "$l_messages per day";
$l_oftotal      = "of total";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icqnumber	= "ICQ Number";
$l_icqadd	= "Add";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger 	= "MSN Messenger";
$l_website 	= "홈페이지";
$l_occupation 	= "직업";
$l_interests 	= "분야";
$l_signature 	= "서명";
$l_sigexplain 	= "이것은 텍스트 블럭으로 당신이 남기는 게시물의 하단부에 삽입됩니다. <br> 최대 영문 255자 한글 127자 까지 가능합니다!";
$l_usertaken	= "The $l_username you picked has been taken.";
$l_userdisallowed= "The $l_username you picked has been disallowed by the administrator. $l_tryagain";
$l_infoupdated	= "당신의 정보가 업데이트(Update)되었습니다.";
$l_publicmail	= "다른 사용자 에게 나의 $l_emailaddress 를 공개합니다.";
$l_itemsreq	= "* 표시가 있는 부분은 필수입력부분 입니다.";

// Viewforum
$l_viewforum	= "View Forum";
$l_notopics	= "이 주제에는 글이 없습니다. 새로 글을 남길수 있습니다.";
$l_hotthres	= "More then $hot_threshold $l_posts";
$l_islocked	= "$l_topic is Locked (No new $l_posts may be made in it)";
$l_moderatedby	= "관리자";

// Private forums : 허가제 포럼
$l_privateforum	= "이곳은 <b>허가제 포럼</b>.";
$l_private 	= "$l_privateforum<br>Note : 허가를 위해서 쿠키 기능을 켜두어야 합니다.";
$l_noprivatepost = "$l_privateforum You do not have access to post to this forum.";

// Viewtopic
$l_topictitle	= "View $l_topic";
$l_unregistered	= "등록되지 않은 사용자";
$l_posted	= "글쓴때";
$l_profileof	= "사용자 정보 보기 =>";
$l_viewsite	= "사용자의 홈페이지로 가기 =>";
$l_icqstatus	= "$l_icq status";  // ICQ status
$l_editdelete	= "$l_post 수정하기";
$l_replyquote	= "이글을 인용하여 글쓰기";
$l_viewip	= "View Posters IP (Moderators/Admins Only)";
$l_locktopic	= "Lock this $l_topic";
$l_unlocktopic	= "Unlock this $l_topic";
$l_movetopic	= "이동하기 this $l_topic";
$l_deletetopic	= "지우기 this $l_topic";

// Functions
$l_loggedinas	= "다음 이름으로 로그임 된 상태 : ";
$l_notloggedin	= "로그인 되지 않은상태";
$l_logout	= "로그아웃[나가기]";
$l_login	= "로그인[들어오기]";

// Page_header
$l_separator	= "� �";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "개인정보 수정";
$l_editprefs	= "Edit $l_preferences";
$l_search	= "찾기";
$l_memberslist	= "회원목록";
$l_faq		= "FAQ";
$l_privmsgs	= "쪽지";
$l_sendpmsg	= "쪽지보내기";
$l_statsblock   = '$statsblock = "총$total_posts 개의 글이 있습니다. <br>
총 회원수 -$total_users- Registered Users.<br>
가장 최근에 등록한 사용자는 -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"명의 사용자가":"명의 사용자들이") ." <a href=\"$online_url\">이런일을 하고있음</a><br>";';
$l_privnotify   = '$privnotify = "<br>You have $new_message <a href=\"$privmsg_url\">new private ".($new_message>1?"messages":"message")."</a>.";';

// Page_tail
$l_adminpanel	= "관리도구";
$l_poweredby	= "Powered by";
$l_version	= "Version";

// Auth

// Register
$l_notfilledin	= "Error - 필요한 모든 항목을 채워주세요.";
$l_invalidname	= "선택하신 아이디, \"$username\" 는 이미 사용되고 있습니다.";
$l_disallowname	= "선택하신 아이디, \"$username\" 는 관리자가 허가하지 않는 아이디 입니다.";

$l_welcomesubj	= "$sitename 에 오신것을 환영합니다.";
$l_welcomemail	=
"
$l_welcomesubj,

WELCOME !!

이 편지를 중요하게 간직하십시요.
가능하다면 지우지 않는것이 좋습니다.

[모임 이름(forum name)] 사용자 정보는 다음과 같습니다.

----------------------------
아이디: $username
암호: $password
----------------------------

이 암호를 잊어버리지 않도록 주의하십시요.
이것은 또한번 암호화 되어 데이터베이스에 기록됩니다.
따라서 관리자도 여러분의 암호를 알아낼수 없습니다.

그러나, 당신이 암호를 잊었을 경우 우리는 쉬운 스크립트를
통해 임의의 암호를 생성, 메일로 보내드립니다.

등록해 주셔서 감사합니다.
당신의 활발한 활동 기대하겠습니다.

[모임 이름(forum name)] 관리자 드림

$email_sig
";
$l_beenadded	= "정보가 데이터베이스에 추가되었습니다.";
$l_thankregister= "등록해 주셔서 감사합니다!";
$l_useruniq	= "중복된 아이디를 허락하지 않습니다.";
$l_storecookie	= "나의 아이디를 1년간 쿠키에 기록합니다.";

// Prefs
$l_prefupdated	= "$l_preferences updated. $l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex";
$l_editprefs	= "사용자 정보 $l_preferences";
$l_themecookie	= "NOTE: 아래의 설정을 사용하기 위해서는 <b>반드시</b> 쿠키(Cookies)를 허용하여야 합니다.";
$l_alwayssig	= "언제나 내가 쓰는 글에 서명을 추가함";
$l_alwaysdisable= "사용하지 않음"; // Only used for next three strings
$l_alwayssmile	= "$l_smilies $l_alwaysdisable";
$l_alwayshtml	= "$l_html $l_alwaysdisable";
$l_alwaysbbcode	= "$l_bbcode $l_alwaysdisable";
$l_boardtheme	= "사이트 개인 테마";
$l_boardlang    = "사이트 언어";
$l_nothemes	= "데이터베이스에 테마가 하나도 없습니다.";
$l_saveprefs	= "$l_preferences 저장하기";

// Search
$l_searchterms	= "키워드";
$l_searchany	= " 지정한 검색어 중의 하나라도 포함되는 게시물을 찾아서 보여줍니다. (OR검색,기본값)";
$l_searchall	= "지정한 검색어가 모두 포함되는 게시물을 검색해줍니다. (AND검색)";
$l_searchallfrm	= "모든 주제에서 찾기";
$l_sortby	= "정렬";
$l_searchin	= "찾기";
$l_titletext	= "Title & Text";
$l_search	= "검색";
$l_nomatches	= "검색결과가 없습니다. 검색 키워드를 좀더 넓은 범위로 확장하세요.";

// Whosonline
$l_whosonline	= "로그인된 사용자";
$l_nousers	= "현재 로그인된 사용자가 없습니다.";


// Editpost
$l_notedit	= "다른사람이 쓴 글은 수정할수 없습니다.";
$l_permdeny	= "이전에 입력한 암호와 일치하지 않거나 권한이 없습니다.  $l_tryagain";
$l_editedby	= "이 $l_post 는(은) 수정됨 by :";
$l_stored	= "$l_post 이 데이터베이스에 저장되었습니다.";
$l_viewmsg	= "to view your $l_post.";
$l_deleted	= "당신이 쓴 $l_post 은 이미지워졌습니다.";
$l_nouser	= "그 $l_post 는(은) 존재하지 않습니다.";
$l_passwdlost	= "암호 분실!";
$l_delete	= "이 글을 삭제함 ";

$l_disable	= "막음";
$l_onthispost	= "이 글에서";

$l_htmlis	= "$l_html is";
$l_bbcodeis	= "$l_bbcode is";

$l_notify	= "이글에 대한 답변글을 메일로 받아봄";

// Newtopic
$l_emptymsg	= "$l_post 에 내용이 없습니다. $l_post 에 내용을 채워 주세요.";
$l_aboutpost	= "글쓰기에 관하여";
$l_regusers	= "All <b>Registered</b> users";
$l_anonusers	= "<b>Anonymous</b> users";
$l_modusers	= "Only <B>Moderators and Administrators</b>";
$l_anonhint	= "<br>(익명으로 사용하기 위해서는 아이디와 암호를 입력하지 마세요)";
$l_inthisforum	= "can post new topics and replies to this forum";
$l_attachsig	= "언제나 서명 보임";
$l_cancelpost	= "Cancel Post";

// Reply
$l_nopostlock	= "이 주제에 대해 답변을 남길수 없습니다. 이 주제는 잠겨있습니다. You cannot post a reply to this topic, it has been locked.";
$l_topicreview  = "Topic Review";
$l_notifysubj	= "A reply to your topic has been posted.";
$l_notifybody	= 'Dear $m[username]\r\nYou are receiving this Email because a message
당신이 $sitename 에 남긴 게시물에 답변이 올라왔습니다.
you posted on $sitename forums has been replied to, and
you selected to be notified on this event.

이 링크를 누르면 게시물을 볼수 있습니다.
You may view the topic at:

http://$SERVER_NAME$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum

또는 이 링크를 누르면 $sitename 포럼 처음으로 이동합니다.
Or view the $sitename forum index at

http://$SERVER_NAME$url_phpbb

$sitename 을(를)이용해 주셔서 감사합니다.
Thank you for using $sitename forums.

좋은 하루 보내세요~~ ; )
Have a nice day.

$email_sig';


$l_quotemsg	= '[quote]\nOn $m[post_time], $m[username] wrote:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "당신이 $l_message 에게 쪽지를 보내려면 받는사람의 아이디를 입력해야 합니다.";
$l_sendothermsg	= "Send another Private Message";
$l_cansend	= "can send $l_privmsgs";  // All registered users can send PM's
$l_yourname	= "당신의 $l_username";
$l_recptname	= "받는사람의 $l_username";

// Replypmsg
$l_pmposted	= "Reply Posted, you can click <a href=\"viewpmsg.$phpEx\">here</a> to view your $l_privmsgs";

// Viewpmsg
$l_nopmsgs	= "당신은 $l_privmsgs 를 하나도 가지고 있지 않네요...";
$l_reply	= "답장";

// Delpmsg
$l_deletesucces	= "성공적으로 삭제됨.";

// Smilies
$l_smilesym	= "What to type";
$l_smileemotion	= "Emotion";
$l_smilepict	= "Picture";

// Sendpasswd
$l_wrongactiv	= "The activation key you provided is not correct. Please check email $l_message you recived and make sure you have copied the activation key exactly.";
$l_passchange	= "당신의 암호가 성공적으로 변경되었습니다.  <a href=\"bb_profile.$phpEx?mode=edit\">개인정보 수정</a>으로 가셔서 당신의 암호를 변경하여 주세요.";
$l_wrongmail	= "입력하신 메일 주소는 가입하실때 입력하신 메일 주소와 틀립니다.";

$l_passsubj	= "$sitename 포럼 암호가 변경됨";

$l_pwdmessage	= '$checkinfo[username] 님 안녕하세요,
$checkinfo[username] 님(혹은 다른 누군가가) [포럼이름(forum name)] 의 암호 변경을 신청하였습니다.
만약 $checkinfo[username] 님께서 신청하지 않았다면 지금 당장 이 편지를 지워버리십시요.
그러면 암호는 종전과 동일하게 유지됩니다.

You are receiving this email because you (or someone pretending to be you)
has requested a passwordchange on $sitename forums. If you believe you have
received this message in error simply delete it and your password will remain
the same.

서버에서 임의로 생성된 [포럼 이름(forum name)]의 암호는 $newpw 입니다.
Your new password as generated by the forums is: $newpw

이 암호를 사용하시려면 아래의 링크를 방문해 주세요.
In order for this change to take effect you must visit this page:

   http://$SERVER_NAME$PHP_SELF?actkey=$key

이 페이지를 방문하시면 당신의 암호는 자동으로 $newpw 으로 변경됩니다.
변경이 완료되면 [개인정보수정]으로 가셔서 새 암호로 변경해 주세요.
Once you have visited the page your password will be changed in our database,
and you may login to the profile section and change it as desired.

$sitename 을 이용하여 주셔서 감사합니다.
Thank you for using $sitename Forums

$email_sig';

$l_passsent	= "당신의 비밀번호는 서버에서 임의의 비밀번호로 변경되었습니다. 변경된 비밀번호에 대한 정보는, 발송한 메일을 참고하세요.";
$l_emailpass	= "이메일로 비밀번호 전송";
$l_passexplain	= "아래의 양식을 채우시면, 새로운 비밀번호에 대한 정보가 당신의 메일로 발송됩니다.";
$l_sendpass	= "비밀번호 전송";


?>
