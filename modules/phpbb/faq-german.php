<?php
/***************************************************************************
                          faq.php  -  description
                             -------------------
    begin                : Fri November 3, 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "FAQ";
$pagetype = "other";
include('page_header.'.$phpEx);
?>

<div align="center"><center>
<table border="0" width="100%" cellpadding="6" bgcolor=>
    <tr bgcolor="<?php echo $color1?>">
        <td><font size="<?php echo $FontSize4?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>"><b>Frequently
          Asked Questions / Oft gestellte Fragen und Antworten</font></b></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $linkcolor?>">
          <a href="#register">Muss ich mich registrieren?</a><br>
          <a href="#smilies">Kann ich Smileys benutzen?</a><br>
          <a href="#html">Kann ich HTML verwenden?</a><br>
          <a href="#bbcode">Was ist BBCode? kann ich BBCode verwenden?</a><br>
          <a href="#mods">Was sind Moderatoren?</a><br>
	  <a href="#profile">Kann ich mein Benutzerprofil &auml;ndern?</a><br>
          <a href="#prefs">Kann ich das Aussehen des Forums f&uuml;r mich anpassen?</a><br>
          <a href="#cookies">Brauche ich Cookies? / Was sind Cookies?</a><br>
          <a href="#edit">Kan ich eigene Beitr&auml;ge &auml;ndern?</a><br>
          <a href="#attach">Kann ich an meine Beitr&auml;ge Dateien anh&auml;ngen?</a><br>
          <a href="#search">Kann ich nach Beitr&auml;gen suchen?</a><br>
          <a href="#signature">Kann ich meine Signatur an meine Beitr&auml;ge anf&uml;gen?</a><br>
          <a href="#announce">Was sind Ank&uuml;ndigungen?</a><br>
          <a href="#pw">Ich habe mein Passwort vergessen. Was kann ich tun?</a><br>
          <a href="#notify">Kann ich per email benachrichtigt werden, sobald jemand auf mein Thema antwortet?</a><br>
          <a href="#searchprivate">Kann ich private Foren durchsuchen?</a><br>
          <a href="#ranks">Was sind R&auml;nge in den <?php echo $sitename?> Foren?</a><br>
          <a href="#rednumbers">Warum brennen die Ordner in der Liste der Themen?</a></p></font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
        <font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <a name="register"><b><br>Registrieren</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Registration ist nur f&uuml;r bestimmte Foren notwendig. Abh&auml;ngig davon, wie der
	Administrator die Foren eingerichtet hat, m&uuml;ssen Sie bei einigen Foren als Benutzer
	registriert und angemeldet sein, in anderen Foren k&ouml;nnen Sie schreiben, ohne registriert zu sein.
	Falls anonymes Schreiben m&ouml;glich ist, lassen Sie einfach an den entsprechenden Stellen die Felder
	f&uuml;r Benutzername und Passwort frei.
	Die Registrierung ist kostenlos, Sie m&uuml;ssen nicht unbedinge Ihren richtigen Namen angeben.
	Sie m&uuml;ssen allerdings Ihre korrekte email-Adresse angeben, denn falls Sie Ihr Passwort vergessen haben,
	k&ouml;nnen Sie sich auf diesem Weg ein neues zuschicken lassen.
	Sie k&ouml;nnen Ihre email-Adresse vor allen ausser dem Administrator geheim halten, wenn Sie das w&uuml;nschen.
	Diese Option ist bereits voreingestellt; Sie k&ouml;nnen anderen Benutzern des Forums erm&ouml;glichen,
	Ihre email-Adresse zu sehen, wenn Sie bei der Registrierung oder sp&auml;ter in Ihrem Benutzerprofil die
	entsprechende Option anklicken.
	Wenn Sie jetzt registrieren m&ouml;chten, klicken Sie
	<a href="<?php echo $url_phpbb?>/bb_register.<?php echo $phpEx?>?mode=agreement">hier...</a></font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="smilies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <b><br>Smilies</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Vielleicht kennen Sie Smileys schon von emails oder anderen Forumsystemen.
	Smileys sind kleine Gesichter, die Sie im Text Ihrer Beitr&auml;ge verwenden k&ouml;nnen, um zum Beispiel
	Ironie oder Entt&auml;uschung anzudeuten, oder einfach um die Nachrichten aufzulockern, zum Beispiel ein
	l&auml;chelndes Smiley...
	:)
	oder ein frutrierter...
	:(.
	Dieses Forumsystem verwandelt im Text der Beitr&auml;ge angegebene Smileys automatisch zu den entsprechenden kleinen
	Bildchen.
	Sie k&ouml;nnen zwischen den folgenden Smileys w&auml;hlen:</font><BR>
	<table width="50%" ALIGN="CENTER" BGCOLOR="<?php echo $table_bgcolor?>" CELLSPACEING=1 BORDER="0">
	  <TR><TD>
	  <TABLE WIDTH="100%" BORDER="0">
		 <TR BGCOLOR="<?php echo $color1?>">
		 <TD width="100">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		 		<?php echo $l_smilesym?>
		 	</FONT>
		 </td>
		 <td width="50%">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo $l_smileemotion?>
			</FONT>
		</td>
		<td width="55">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo $l_smilepict?>
			</FONT>
		</td></tr>
 <?php

	  if ($getsmiles = mysql_query("SELECT * FROM smiles")) {
	     while ($smile = mysql_fetch_array($getsmiles)) {
?>
		 <TR BGCOLOR="<?php echo $color2?>">
		 <TD width="100">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
		 		<?php echo stripslashes($smile[code])?>
		 	</FONT>
		 </td>
		 <td width="50%">
		 	<FONT FACE="<?php echo $FontFace?>" SIZE="<?php echo $FontSize2?>" COLOR="<?php echo $textcolor?>">
				<?php echo stripslashes($smile[emotion])?>&nbsp;
			</FONT>
		</td>
		<td width="55">
			<IMG SRC="<?php echo "$url_smiles/$smile[smile_url]";?>">
		</td></tr>
<?php
	     }
	  } else
	     echo "Fehler 102: Konnte Smileys nicht in der Datenbank finden.";
?>
    </TABLE></TABLE>
    </div>
	</td>
    </tr>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="html">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b><br>Verwendung von HTML</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
	<td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Sie k&ouml;nnen HTML-Codes in Ihren Beitr&auml;gen nutzen, wenn der Administrator oder die Moderatorin das in
	Ihrem Forum aktiviert haben.
	Bei jedem Beitrag, den Sie schreiben, wird Ihnen angezeigt, ob Sie BBCode und/oder HTML verwenden k&ouml;nnen.
	Wenn HTML aktiviert ist, k&ouml;nnen Sie beliebige HTML-tags verwenden, aber achten Sie bitte auf korrekte
	Syntax. Falls nicht, muss die Administratorin oder der moderator des Forums Ihren Beitrag nachtr&auml;glich &auml;ndern.
	Vergessen Sie das Gesagte gleich wieder, wenn Sie nicht wissen, was HTML oder BBCode ist.
	</td>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="bbcode">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b><br>Verwendung von BBCode / Was ist BBCode?</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
	<td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
BBCode ist eine Variante von HTML-Tags, die Sie vielleicht schon kennen. Grunds&auml;tzlich k&ouml;nnen Sie mit BBCode
Formatierungen in Ihren Text einbringen, die normalerweise HTML erfordern w&uuml;rden, so zum Beispiel <b>Fettdruck</b>.
Selbst wenn HTML f&uuml;r das Forum deaktiviert ist, k&ouml;nnen Sie BBCode verwenden.
BBCode ist eine Alternative zu HTML-Code, weil BBCode weniger Fachwissen ben&ouml;tigt und sicherer zu benutzen ist:
Eventuell falsche Syntax wird Ihre Seite nicht unleserlich machen.
<P>

<table border=0 cellpadding=0 cellspacing=0 width="<?php echo $tablewidth?>" align="CENTER"><TR><td bgcolor="#FFFFFF">
<table border=0 cellpadding=4 border=0 cellspacing=1 width=100%>
<TR bgcolor="<?php echo $color1?>">
<TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Verweise auf Internetadressen (URLs) einbinden</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD><FONT SIZE="2" FACE="Verdana, Arial">
Falls BBCode in Ihrem Forum aktiviert ist, brauchen Sie nicht mehr den [URL] -Code, um einen Hyperlink zu erzeugen.
Schreiben Sie einfach den kompletten Link auf eine der folgenden Wege und der Hyperlink wird automatisch erstellt.
Sie k&ouml;nnen so in Ihrem Beitrag ganz einfach auf eine beliebige andere Internetseite verweisen, die dann durch einen
einfachen Klick erreichbar ist:

<UL><FONT SIZE="2" FACE="Verdana, Arial">
<LI> http://www.IhreAdresse.de
<LI> www.IhreAdresse.com
</UL>
</font>

Benutzen Sie entweder die komplette http:// -Adresse oder k&uuml;rzen Sie mit www. ab. Wenn die Seite, auf die Sie
verweisen m&ouml;chten, nicht mit www. beginnt, m&uuml;ssen Sie die komplette URL http:// verwenden.
Sie k&ouml;nnen auch https:// oder ftp:// -Adressen in Links verwenden (falls BBCode eingeschaltet ist).
<P>
Der alte [URL] Code funktioniert weiterhin, wie weiter unten beschrieben.
Umklammern Sie einfach den Link wie folgt (BBCode ist <FONT COLOR="#FF0000">rot</FONT> markiert).
<P><center>
<FONT COLOR="#FF0000">[url]</FONT>www.dellekom.de<FONT COLOR="#FF0000">[/url]</FONT>
<P></center>
Auch richtige Hyperlinks k&ouml;nnen Sie mit dem [url] -Code einbinden. Das Format ist:
<BR><center>
<FONT COLOR="#FF0000">[url=http://www.dellekom.de]</font>dellekom.de<FONT COLOR="#FF0000">[/url]</font>
</center><p>
In den bisher genannten Beispielen erzeugt BBCode automatsich einen Hyperlink zur umklammerten URL (Internetadresse).
BBCode stellt auch sicher, dass der link in einem neuen Fenster ge&ouml;ffnet wird, wenn der Besucher auf den Link klickt.
Beachten Sie, dass der Teil "http://" der URL optional ist. Mit dem zweiten der obigen Beispiele k&ouml;nnen Sie die URL zu einer
beliebigen Seite lenken, die nach dem Gleichheitszeichen steht.
Bitte beachten Sie, dass Sie innerhalb der URL-Adresse KEINE Anf&uuml;hrungszeichen verwenden.
</font>
</td>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
email-Adressen einbinden</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Um eine email-Adresse innerhalb Ihrer Nachricht anzugeben, umklammern Sie die Adresse wie im folgenden Beispiel:
(BBCode ist <FONT COLOR="#FF0000">rot</FONT> markiert).
<P>
<CENTER>
<FONT COLOR="#FF0000">[email]</FONT>help@dellekom.de<FONT COLOR="#FF0000">[/email]</FONT>
</CENTER>
<P>
In diesem Beispiel erzeugt BBCode daraus einen Link, der automatisch das email-Programm des Besuchers startet, um
an help@dellekom.de eine email schreiben zu k&ouml;nnen.
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Fettdruck und Kursivschrift</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Sie k&ouml;nnen Text mit den [b] [/b] oder [i] [/i] - Markierungen (Tags) <b>fettdrucken</b> oder <i>schr&auml;gstellen</i>
<P>
<CENTER>
Hello, <FONT COLOR="#FF0000">[b]</FONT><B>Tom</B><FONT COLOR="#FF0000">[/b]</FONT><BR>
Hello, <FONT COLOR="#FF0000">[i]</FONT><I>Jerry</I><FONT COLOR="#FF0000">[/i]</FONT>
</CENTER>
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Aufz&auml;hlungen und Listen</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Sie k&ouml;nnen auch Listen mit Aufz&auml;hlungspunkten (Punkt oder Zahl) erzeugen:
<P>
Liste mit Aufz&auml;hlungspunkten:
<P>
<FONT COLOR="#FF0000">[list]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> Dieses ist der erste Streich.<BR>
<FONT COLOR="#FF0000">[*]</font> Und der zweite folgt sogleich.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
Dies erzeugts:
<ul>
<LI> Dieses ist der erste Streich.
<LI> Und der zweite folgt sogleich.
</ul>

Beachten Sie, dass Sie jede Liste mit [/list] abschliessen m&uuml;ssen.

<P>
Numerierte Listen sind genauso einfach. Schreiben Sie einfach [LIST=A] oder [LIST=1].
[List=A] erzeugt eine Liste, die von A bis Z numeriert ist, [List=1] erzeugt eine numerierte Liste.
<P>
Hier ist ein Beispiel:
<P>

<FONT COLOR="#FF0000">[list=A]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> Dieses ist der erste Streich.<BR>
<FONT COLOR="#FF0000">[*]</font> Und der zweite folgt sogleich.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
This produces:
<ol type=A>
<LI> Dieses ist der erste Streich.
<LI> Und der zweite folgt sogleich.
</ul>


</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Bilder hinzuf&uuml;gen</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Verwenden Sie einfach den [URL] - Code wie im folgenden Beispiel, um ein Bild in Ihren Text einzubinden.
 (BBCode ist <FONT COLOR="#FF0000">rot</FONT> dargestellt).
<P>
<CENTER>
<FONT COLOR="#FF0000">[img]</FONT>http://www.totalgeek.org/images/tline.gif<FONT COLOR="#FF0000">[/img]</FONT>
</CENTER>
<P>
In diesem Beispiel bindet BBCode das Bild automatisch in Ihren Beitrag ein. Beachten Sie bitte, dass der
"http://" -Teil der URL f&uuml;r den  <FONT COLOR="#FF0000">[img]</FONT> -Code unverzichtbar ist.
</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Zitieren anderer Beitr&auml;ge</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Um eine bestimmte Stelle aus einem anderen Beitrag zu zitieren oder kommentieren, ben&uuml;tzen Sie einfach
"Kopieren" und "Einf&uuml;gen" in Ihrem Browser und umklammern Sie das Zitat wie folgt:
(BBCode ist wieder <FONT COLOR="#FF0000">rot</FONT> markiert).
<P>
<CENTER>
<FONT COLOR="#FF0000">[QUOTE]</FONT>Ask not what your country can do for you....<BR>ask what you can do for your country.<FONT COLOR="#FF0000">[/QUOTE]</FONT>
</CENTER>
<P>
In diesem Beispiel r&uuml;ckt BBCode diesen Text als Zitat ein.</FONT>
</td>
</tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Code Tag</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
&Auml;hnlich wie beim "Quote"-Tag [QUOTE] k&ouml;nnen Sie mit dem &lt;PRE&gt; Tag den Zeilenumbruch erhalten.
Das ist bei der Wiedergabe von Gedichten oder von Programmcode sinnvoll.
<P>

<FONT COLOR="#FF0000">[CODE]</FONT>#!/usr/bin/perl
<P>
print "Content-type: text/html\n\n";
<BR>
print "Hallo Welt!";
<FONT COLOR="#FF0000">[/CODE]</FONT>

<P>
In diesem Beispiel r&uuml;ckt BBCode den Text als Zitat ein und erh&auml;lt zus&auml;tzlich den
Zeilenumbruch.
</FONT>
</td>
</tr>
</table>
</td></tr></table>
</blockquote>
<BR>
<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
Sie m&uuml;ssen nicht HTML <b>und</b> BBCode verwenden, um dasselbe zu erreichen.
BBCode beachtet nicht die Gross-/Kleinschreibung (Sie k&ouml;nnen <FONT COLOR="#FF0000">[URL]</FONT> oder
<FONT COLOR="#FF0000">[url]</FONT> gleichermassen verwenden).
<P>
Falsche Verwendung von BBCode:
<P>
<FONT COLOR="#FF0000">[url]</FONT> www.totalgeek.org <FONT COLOR="#FF0000">[/url]</FONT> - Bitte keine Leerzeichen zwischen
den Tags und dem umklammerten Text verwenden.
<P>
<FONT COLOR="#FF0000">[email]</FONT>help@dellekom.de<FONT COLOR="#FF0000">[email]</FONT> - Das abschliessende Tag muss einen
Schr&auml;gstrich / enthalten: (<FONT COLOR="#FF0000">[/email]</FONT>)
<P>
</FONT>
</B>
	</td>
	    <tr bgcolor="<?php echo $color1?>">
        <td nowrap>
	<p align="left"><a name="mods">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Moderatoren</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <p>
	    <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	    Moderatoren pflegen die Foren.
	    Sie k&ouml;nnen jeden Beitrag im Forum &auml;ndern oder l&ouml;schen.
	    Wenn Sie eine Frage zu einem bestimmten Forum haben, wenden Sie sich am besten direkt an den
	    jeweiligen Moderator des Forums.
	    </p>
          <p>Administratoren und Forum-Moderatoren behalten sich das Recht vor, Beitr&auml;ge oder Themen zu l&ouml;schen,
          die keinen klare und zweckm&auml;ssigen Text enthalten.
          Es gibt viele Besucher, die noch mit relativ langsamen Modems im Internet unterwegs sind, und die sich
          nicht durch endlose fruchtlose Diskussionen arbeiten m&ouml;chten.</p>
          <p>Wer nur Beitr&auml;ge schreibt, um seine / ihre Anzahl der Beitr&auml;ge zu erh&ouml;hen oder sich anders kontraproduktiv
          verh&auml;lt, kann vom Forum ausgeschlossen werden. Kritische Beitr&auml;ge sind willkommen, solange sie nicht
          pers&ouml;nlich verletzen.</p>
          <p>†berlegen Sie sich, ob der Titel Ihres Themas passt: Titel wie "Unbedingt lesen!" usw. machen wenig Sinn.
          Sagen Sie gleich kurz und pr&auml;zise, worum es in Ihrem Beitrag geht.</font></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<p align="left"><a name="profile">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>&Auml;ndern Ihrer Einstellungen / Benutzerprofil</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Sie k&ouml;nnen einfach die Einstellungen &auml;ndern, mit denen Sie das Forum besuchen:
	klicken Sie auf den Link &quot;Benutzerprofil&quot; in der Auswahl oben auf jeder Seite.
	Identifizieren Sie sich durch Ihren Benutzernamen und Ihr Passwort, und schon k&ouml;nnen Sie
	alle Einstellungen lesen und &auml;ndern.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="prefs">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Anpassen Ihrer Einstellungen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Als registrierter Benutzer k&ouml;nnen Sie Ihren Benutzernamen f&uuml;r ein Jahr speichern lassen.
	Dadurch kann die Forumsoftware Sie erkennen, sobald Sie wieder das Forum betreten.
	Sie k&ouml;nnen sogar die Voreinstellung der Farben und Schriftgr&ouml;ssen, mit denen Sie das Forum sehen,
	in gewissen Grenzen ver&auml;ndern.
	Falls es die Administratorin erlaubt hat, k&ouml;nnen Sie sogar eigene Einstellungsschablonen
	(Farben, Schriftarten, Schriftgr&ouml;ssen, Hintergrundfaben) abspeichern.
	Nur der Administrator kann das verwendete Titellogo &auml;ndern.
	Wenn Sie eine neue Schablone erzeugen, wird das Standard-Titelbild verwendet.
	<br><b>ACHTUNG: Um diese Schablonen zu nutzen, m&uuml;ssen Sie in Ihrem Browser Cookies aktiviert haben.<b></font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="cookies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Cookies</b></font></a></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Dieses Forum nutzt Cookies um folgende Informationen in Ihrem Browser abzuspeichern:
	Das Datum Ihres letzten Besuches (um Ihnen anzuzeigen, wo seither neue Beitr&auml;ge entstanden sind),
	Ihren Benutzername (um sich Ihnen mit Ihren pers&ouml;nlichen Einstellungen begr&uuml;ssen zu k&ouml;nnen)
	und eine eindeutige Serinnummer, wenn Sie sich anmelden.
	Wenn Ihr Browser keine Cookies unterst&uuml;tzt oder Cookies abgeschaltet haben, arbeiten die genannten
	Funktionen nicht richtig.
	Die Informationen werden an niemanden &uuml;bermittelt und enthalten keinerlei weitere Daten.
	Ein Browser schickt ein Cookie vor Abruf einer Seite nur an diejenige Adresse zur&uuml;ck,
	von der er ein Cookie erhalten hat.
	Cookies k&ouml;nnen weder Ihr System ausspionieren noch etwas in Ihrem System ver&auml;ndern.
	Um gleichwohl zu verhindern, dass Werbefirmen wie z.B. doubleclick.com ein Nutzerprofil von Ihnen
	selbstt&auml;tig erstellen k&ouml;nnen (da deren Werbebanner auf vielen anderen Seiten eingeblendet sind),
	sollten Sie von Zeit zu Zeit Ihnen unbekannte Cookies l&ouml;schen.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="edit">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>&Auml;ndern Ihrer Beitr&auml;ge</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Die von Ihnen erstellten Beitr&auml;ge k&ouml;nnen Sie jederzeit &auml;ndern.
	Gehen Sie einfach zu Ihrem Beitrag. Unter dem Beitrag befindet sich ein &quot;&auml;ndern&qout; - Link.
	Klicken Sie darauf, es &ouml;ffnet sich ein Fenster, in dem Sie Ihren Beitrag nachtr&auml;glich &auml;ndern k&ouml;nnen.
	Nur Sie, die Administratorin und der Moderator k&ouml;nnen Ihren Beitrag &auml;ndern.
	Bis zu 30 Minuten nach Absenden Ihrer Nachricht k&ouml;nnen Sie den Beitrag sogar l&ouml;schen.
	Sp&auml;ter kann nur noch der Administrator oder die Moderatorin den Beitrag l&ouml;schen.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="signature">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Signaturen / Unterschriftzeilen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Sie k&ouml;nnen eine Signatur (ein oder zwei Zeilen, in denen z.B. Ihre Adresse oder telefonnummer oder ein
	netter Spruch stehen kann) an jede Ihrer Beitr&auml;ge automatisch anf&uuml;gen lassen.
	In Ihrer Benutzerprofil-Seite k&ouml;nnen Sie diese Signatur einstellen.
	Bitte gegen Sie sparsam mit dieser M&ouml;glichkeit um, da Ihre Signatur sehr oft zu lesen ist, wenn
	Sie viele Beitr&auml;ge schreiben. Pers&ouml;nliche Informationen wie Adresse usw. geh&ouml;ren eher in die sondtigen
	Einstellungen Ihres Benutzerprofils.
	Der Administrator kann die Verwendung der Signaturen zentral abschalten. In diesem Fall sehen Sie kein
	Hinweis auf eine Signatur, auch wenn Sie eine erstellt haben.
	<p>Sie k&ouml;nnen auch  HTML oder <a href="#bbcode">BB Code</a> verwenden, wenn es die Administratorin zugelassen
	hat.</font>
        </p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="attach">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Dateien anh&auml;ngen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Aus Sicherheitsgr&uuml;nden k&ouml;nnen Sie keine Anh&auml;nge verschicken.
	Verwennden Sie f&uuml;r Texte einfach die "Kopieren" und "Einf&uuml;gen"- Funktin Ihres Browsers und &uuml;bernehmen
	Sie den Text direkt in Ihren Beitrag. Word-Dateien zu verschicken ist ohnehin keine gute Idee, da Sie
	dann davon ausgehen, dass alle Ihr Betriebssystem, Ihr Anwendungsprogramm und wahrscheinlich noch in der
	richtigen Version haben m&uuml;ssen. Dies ist jedoch oft nicht der Fall.
	Sie k&ouml;nnen nat&uuml;rlich Hyperlinks auf Dateien anbringen, die Sie anderswo im Internet hinterlegt oder
	gefunden haben.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="search">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Suchen nach bestimmten Beitr&auml;gen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Sie k&ouml;nnen nach bestimmten W&ouml;rtern in den Beitr&auml;gen, im Benutzername oder einem bestimmten Forum
	suchen. Verwenden Sie dazu den Link  &quot;Suchen&quot; oben auf fast allen Seites.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="announce">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Ank&uuml;ndigungen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Forum-weite Ank&uuml;ndigungen sind noch nicht eingebaut, aber f&uuml;r zuk&uuml;nftige Versionen des Forums geplant.
	Der Administrator kann jedoch ein Forum einrichten, in welchem nur moderatoren oder Administratorinnen
	schreiben d&uuml;rfen. Ein solches Forum kann f&uuml;r Ank&uuml;ndigungen verwendet werden.
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="pw">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Sie haben Ihren Benutzernamen und / oder Ihr Passwort vergessen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
 	Das kommt vor... In diesem Fall klicken sie bitte auf &quot;Passwort vergessen?&quot; in der N&auml;he des
 	Passwortfeldes der Eingabemaske f&uuml;r neue Beitr&auml;ge.
 	Dieser Link f&uuml;hrt Sie auf eine Seite, auf der Sie Ihre email-Adresse und einen Benutzernamen
 	eingeben k&ouml;nnen. Das Forum erkennt Ihre email-Adresse und sendet Ihnen ein zuf&auml;llig erzeugtes Passwort
 	per email zu. Mit diesem k&ouml;nnen Sie sich dann anmelden und Ihr Passwort &auml;ndern usw. ...
 	Dieses System funktioniert nur, wenn Sie Ihre korrekte email-Adresse angegeben haben. Anderenfalls
 	m&uuml;ssen Sie sich als ein neuer Benutzer anmelden.
 	</FONT>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="notify">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>email-Benachrichtigung</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Wenn Sie ein neues Thema beginnen, k&ouml;nnen Sie sich die nachfolgenden Antworten dazu automatisch
	per email zuschicken lassen. Kreuzen Sie dazu die &quot;email-Benachrichtigung&quot; auf der Seite an,
	mit der Sie ein neues Thema er&ouml;ffnen.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="searchprivate">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Kann ich private Foren durchsuchen?</b>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Ja, aber Sie k&ouml;nnen keinen der beitr&auml;ge lesen, wenn Sie nicht das jeweilige Passwort zu dem Forum
	kennen. Wenden Sie sich an den Moderator des Forums, vielleicht gibt er Ihnen das Passwort.</font></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="ranks">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Was bedeuten die R&auml;nge im Forum?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Dieses Forum teilt die Benutzer in Gruppen ein, die nach der Anzahl der bisherigen Beitr&auml;ge
	benannt werden. So k&ouml;nnen Sie sehen, ob es sich um einen erfahrenen Nutzer des Forums oder einen
	Neuling handelt. Dies ist hilfreich, wenn Sie Fragen haben.</p>
	<br>
	Die R&auml;nge lauten wie folgt:<br>

	<?php
	$sql = "SELECT * FROM ranks WHERE rank_special = 0";
	if(!$r = mysql_query($sql, $db)) {
	echo "Fehler 102 beim Zugriff auf Datenbank";
	include('page_tail.'.$phpEx);
	exit();
	}
	?>
	<br><TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP"><TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Rang&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Minimum Beitr&auml;ge&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Maximum Beitr&auml;ge&nbsp;</font></TD>
        <TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Bild zum Rang&nbsp;</font></TD>
	</TR>
	<?php
	if($m = mysql_fetch_array($r)) {
	do {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_title]</font></TD>";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_min]</font></TD>";
	echo "<TD><font face=\"<?php echo $FontFace?>\" size=\"2\" color=\"$textcolor\">$m[rank_max]</font></TD>";
	// The rank image has not been implemented at this time.
        if($m[rank_image] != '')
	   echo "<TD><img src=\"$url_images/$m[rank_image]\"></TD>";
	else
	   echo "<TD>&nbsp;</TD>";
	echo "</TR>";
	} while($m = mysql_fetch_array($r));
	}
	else {
	echo "<TR BGCOLOR=\"$color2\" ALIGN=\"CENTER\">";
	echo "<TD COLSPAN=\"4\"><font face=\"<?php echo $FontFace?>\" size=\"2\">Keine R&auml;nge gespeichert</font></TD>";
	echo "</TR>";
	}
	?>
	</TABLE></TABLE></font>
	<br>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Der Administrator hat auch die M&ouml;glichkeit, an einzelne Benutzer bestimmte R&auml;nge zu vergeben. Die Tabelle
	enth&auml;lt nicht diese speziellen R&auml;nge.</font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="rednumbers">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b><br>Warum </b>
	</font>
	<font color="#FF0033" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>brennen</b>
	</font>
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b> manche Ordner in der Forenliste?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Brennde Ordnersymbole zeigen an, dass in diesem Forum mehr als <?php echo $hot_threshold?> sind.
	Dies ist ein Hinweis an Personen mit langsamer Internetverbindung, dass der Aufbau der
	Seite etwas l&auml;nger dauern k&ouml;nnte.</font></p>
        </td>
    </tr>
</table>
</center>
</div>

<?php
include('page_tail.'.$phpEx);
?>
