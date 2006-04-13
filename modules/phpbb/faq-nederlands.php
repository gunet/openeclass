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
/***************************************************************************
 * Created by: Steven Cunningham (defender@webinfractions.com) for phpBB
 * Translated by: los3r
 * *************************************************************************/
include('extention.inc');
include('functions.'.$phpEx);
include('config.'.$phpEx);
require('auth.'.$phpEx);
$pagetitle = "FAQ";
$pagetype = "other";
include('page_header.'.$phpEx);
?>

<div align="center"><center>
<table border="0" width="<?php echo $tablewidth?>" bgcolor="<?php echo $table_bgcolor?>">
  <TR><TD>
<table border="0" width="100%" bgcolor=>
    <tr bgcolor="<?php echo $color1?>">
        <td><font size="<?php echo $FontSize4?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>"><b>Veel Gestelde Vragen
		</font></b></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $linkcolor?>">
				<a href="#register">Moet ik me registreren?</a><br>
				<a href="#smilies">Smilies gebruiken</a><br>
				<a href="#html">HTML gebruiken</a><br>
				<a href="#bbcode">BB Code gebruiken</a><br>
				<a href="#mods">Wat zijn moderators?</a><br>
				<a href="#profile">Kan ik mij profiel aanpassen?</a><br>
				<a href="#prefs">Kan ik de kleuren veranderen?</a><br>
				<a href="#cookies">Hoe worden cookies gebruikt?</a><br>
				<a href="#edit">Kan ik mijn eigen berichten bewerken?</a><br>
				<a href="#attach">Kan ik bestanden bijvoegen?</a><br>
				<a href="#search">Hoe werkt het zoeken?</a><br>
				<a href="#signature">Hoe voeg ik een onderschrift toe aan mijn berichten?</a><br>
				<a href="#announce">Wat zijn aankondigingen?</a><br>
				<a href="#pw">Is er een manier om mijn wachtwoord te achterhalen?</a><br>
				<a href="#notify">Kan ik via mail worden bericht als er iemand op mijn bericht reageert?</a><br>
				<a href="#ranks">Wat zijn de rangen op de <?php echo $sitename?> Forums?</a><br>
				<a href="#rednumbers">Waarom "branden" de iconen in het onderwerp overzicht?</a></p></font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
        <font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <a name="register"><b>Registreren</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
		Afhankelijk van hoe de beheerder de forums ingesteld heeft kan het zijn
		dat je je moet registreren of dat je ook anoniem berichten mag plaatsen.
		Als je anoniem berichten mag plaatsen kan je dat doen door geen
		gebruikersnaam en wachtwoord in te vullen als daarom gevraagd wordt.
		Registratie is kostenloos en het is niet noodzakelijk je echte naam te
		gebruiken. Het is wel een vereiste om je bestaand email adres te gebruiken,
		maar dit zal enkel gebruikt worden om je een nieuw wachtwoord te sturen
		als je je wachtwoord bent vergeten. Je hebt ook de optie om je emailadres
		te verbergen voor iedereen behalve de beheerder.<br>
		Je kunt je aanmelden door te
		<a href="<?php echo $url_phpbb?>/bb_register.<?php echo $phpEx?>?mode=agreement">Registreren</a>.
	</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="smilies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
        <b>Smilies</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Je hebt waarschijnlijk andere gebruikers smilies zien gebruiken in berichten.
	Smilies bestaan uit een aantal tekens bijvoorbeeld een glimlach :) of een frons :(.
	Dit bulletinboard converteerd automatisch bepaalde smilies naar een plaatje.
		De volgende smilies worden momenteel ondersteund:
		</font><BR>
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
	     echo "Could not retrieve from the smile database.";
?>
    </TABLE></TABLE>
    </div>
	</td>
    </tr>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="html">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b>HTML Gebruiken</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
	<td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Je kan gebruik maken van HTML in je berichten als de beheerders deze optie ingeschakeld hebben.
	Iedere keer dat je een nieuw bericht plaatst wordt gemeld of BB Code en/of HTML ingeschakeld zijn.
	Als HTML is ingeschakeld kun je gebruik maken van HTML tags, maar zorg er voor dat je dit zorgvuldig doet
	en geen fouten in je HTML code maakt. Als je dit niet doet kan het nodig zijn de de beheerder of
	moderator je bericht moet bewerken.
	</td>
	<tr bgcolor="<?php echo $color1?>">
	<td>
		<p align="left"><a name="bbcode">
		<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
		<b>BBCode gebruiken</b></font></a></p>
	</td>
	</tr>
	<tr bgcolor="<?php echo $color2?>">
		<td>
		BBCode is een variatie op de HTML tags die je wellicht al kent. In het
		kort komt het er op neer dat je functionaliteit of opmaak aan je bericht
		toevoegd die normaal gesproken alleen met HTML mogelijk is. Je kan BBCode
		gebruiken zelfs als HTML is uitgeschakeld voor het forum dat je gebruikt.
		Het gebruik van BBCode heeft in principe de voorkeur over het gebruik van
		HTML.
		<P>

<table border=0 cellpadding=0 cellspacing=0 width="<?php echo $tablewidth?>" align="CENTER"><TR><td bgcolor="#FFFFFF">
<table border=0 cellpadding=4 border=0 cellspacing=1 width=100%>
<TR bgcolor="<?php echo $color1?>">
<TD>
<FONT SIZE="2" FACE="Verdana, Arial">
URL Hyperlinking</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD><FONT SIZE="2" FACE="Verdana, Arial">
Als BBCode is ingeschakeld voor een forum, dan hoef je niet langer de [URL] code te gebruiken om een hyperlink te maken.
Typ de complete URL in en de hyperlink wordt automatisch gemaakt.<br>

Als de site niet begint met "www" moet je het complete "http://" adres gebruiken.  Het is ook mogelijk om de URL-voorvoegsels https en ftp te gebruiken in auto-link modus (als BBCode is ingeschakeld).
<P>
De oude [URL] code werk ook nog steeds, zoals hieronder toegelicht.

Plaats opmaakcode om de link zoals in het volgende voorbeeld (BBCode is in <FONT COLOR="#FF0000">rood</FONT>).
<P><center>
<FONT COLOR="#FF0000">[url]</FONT>www.appeltaart.nl<FONT COLOR="#FF0000">[/url]</FONT>
<P></center>
Je kan ook echte hyperlinks maken door de [url=] code te gebruiken. Gebruik hiervoor het volgende:
<BR><center>
<FONT COLOR="#FF0000">[url=http://www.totalgeek.org]</font>totalgeek.org<FONT COLOR="#FF0000">[/url]</font>
</center><p>
In de bovenstaande voorbeelden wordt de hyperlink automatisch gemaakt voor de URL die is opgegeven.
Dit zorgt er ook voor dat een link in een nieuw venster wordt geopend als de gebruiker er op klikt.
In het tweede bovenstaande voorbeeld wordt de van de tekst
een hyperlink gemaakt naar de URL die achter het "=" teken staat. Let er ook op dat er geen aanhalingstekens binnen de URL tag
geplaatst mogen worden!
</font>
</td>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Email Links</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Om een link te maken naar een emailadres binnen je bericht gebruik je de opmaak zoals in het volgende voorbeeld (BBCode is in <FONT COLOR="#FF0000">rood</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[email]</FONT>johannus@appeltaart.nl<FONT COLOR="#FF0000">[/email]</FONT>
</CENTER>
<P>
In het bovenstaande voorbeeld, de BBCode maakt automatisch een hyperlink naar het emailadres dat is opgemaakt.
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Vet- en schuingedrukt</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Je kan schuingedrukte tekst of vetgedrukte tekst maken door gedeelten van de tekst op te maken met [i] [/i] resp. [b] [/b] tags.
<P>
<CENTER>
Hallo, <FONT COLOR="#FF0000">[b]</FONT><B>Johannus</B><FONT COLOR="#FF0000">[/b]</FONT><BR>
Hallo, <FONT COLOR="#FF0000">[i]</FONT><I>Maria</I><FONT COLOR="#FF0000">[/i]</FONT>
</CENTER>
</FONT>
</td></tr>
<tr bgcolor="<?php echo $color1?>"><td>
<FONT SIZE="2" FACE="Verdana, Arial">
Bolletjes/Lijsten</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Je kan opsommingslijsten maken met bolletjes of genummerd (op nummer of letter).
<P>
Bolletjes lijst:
<P>
<FONT COLOR="#FF0000">[list]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> Dit is het eerste bolletje.<BR>
<FONT COLOR="#FF0000">[*]</font> Dit is het tweede bolletje.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
Dit levert:
<ul>
<LI> Dit is het eerste bolletje.
<LI> Dit is het tweede bolletje.
</ul>
Let er wel op dat je een afsluitend [/list] moet gebruiken om de lijst te eindigen

<P>
Genummerde lijsten maken is net zo makkelijk. Voeg ofwel [LIST=A] of [LIST=1] toe.
[List=A] typen levert een lijst van A tot Z. [List=1] gebruiken levert een lijst met nummers.
<P>
Hier is een voorbeeld:
<P>

<FONT COLOR="#FF0000">[list=A]</FONT>
<BR>
<FONT COLOR="#FF0000">[*]</font> Dit is het eerste genummerde item.<BR>
<FONT COLOR="#FF0000">[*]</font> Dit is het tweede genummerde item.<BR>
<FONT COLOR="#FF0000">[/list]</font>
<P>
Dit heeft als resultaat:
<ol type=A>
<LI> Dit is het eerste genummerde item.
<LI> Dit is het tweede genummerde item.
</ul>


</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Afbeeldingen toevoegen</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Om een afbeelding in je bericht toe te voegen plaats je opmaak om de URL van de afbeelding zoals in het volgende voorbeeld.
(BBCode is in het <FONT COLOR="#FF0000">rood</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[img]</FONT>http://www.appeltaart.nl/afbeeldingen/kers.gif<FONT COLOR="#FF0000">[/img]</FONT>
</CENTER>
<P>
In het bovenstaande voorbeeld maakt de BBCode automatisch de afbeelding zichtbaar in je bericht. Let op!: het "http://" deel van de
URL is VEREIST bij de <FONT COLOR="#FF0000">[img]</FONT> code. Let er ook op dat sommige forums de <FONT COLOR="#FF0000">[img]</FONT> tag uitzetten om te voorkomen dat er ongewenste afbeeldingen worden toegevoegd.
</FONT>
</td></tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Quoten van andere berichten</font></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Om te referen aan iets specifieks dat iemand anders het geplaatst knip en plak je de bewuste tekst en maak de tekst op zoals hieronder weergegeven (BBCode is in <FONT COLOR="#FF0000">rood</FONT>).
<P>
<CENTER>
<FONT COLOR="#FF0000">[QUOTE]</FONT>Vraag niet wat je medemens voor jouw kan betekenen.... <BR>Vraag wat jij voor je medemens kan beteken.
<FONT COLOR="#FF0000">[/QUOTE]</FONT>
</CENTER>
<P>
In het bovenstaande voorbeeld plaatst de BBCode automatisch blokquotes om de tekst waaraan je refereert. Als je een compleet bericht quote zorg er dan voor dat je maar maximaal 2 of 3 regels van het originele bericht laat staan, dit is vrijwel altijd voldoende en voorkomt ruimteverspilling.</FONT>
</td>
</tr>
<TR bgcolor="<?php echo $color1?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Code Tag</FONT></td></tr>
<TR bgcolor="<?php echo $color2?>"><TD>
<FONT SIZE="2" FACE="Verdana, Arial">
Vergelijkbaar met de Quote tag voegt de Code tag enkele &lt;PRE&gt; tags toe om de opmaak te behouden. Dit is handig voor bijvoorbeeld het weergeven van programmacode.
<P>

<FONT COLOR="#FF0000">[CODE]</FONT>#!/usr/bin/perl
<P>
print "Content-type: text/html\n\n";
<BR>
print "Hallo Wereld!";
<FONT COLOR="#FF0000">[/CODE]</FONT>

<P>
In het bovenstaande voorbeeld plaatst de BBCode automatisch blockquotes om de tekst waaraan je refereert en behoudt de opmaak van de codetekst.</FONT>
</td>
</tr>
</table>
</td></tr></table>
</blockquote>
<BR>
Je moet niet gelijktijdig zowel HTML als BBCode gebruiken om voor dezelfde functionaliteit. Let er ook op dat BBCode geen onderscheid maakt tussen hoofdletters en kleine letters.
(Je kan dus zowel <FONT COLOR="#FF0000">[URL]</FONT> als <FONT COLOR="#FF0000">[url]</FONT>) gebruiken.
<P>
<FONT COLOR="silver">Fout gebruik van BBCode:</FONT>
<P>
<FONT COLOR="#FF0000">[url]</FONT> www.appeltaart.nl <FONT COLOR="#FF0000">[/url]</FONT> - plaats geen spaties tussen de opmaakcode tussen de haken en
de tekst die je opmaakt.
<P>
<FONT COLOR="#FF0000">[email]</FONT>johannus@appeltaart.nl<FONT COLOR="#FF0000">[email]</FONT> - de afsluitende haken moeten een "forward slash" bevatten (<FONT COLOR="#FF0000">[/email]</FONT>)

<P>
</FONT>
</B>
</td>
   <tr bgcolor="<?php echo $color1?>">
        <td nowrap>
	<p align="left"><a name="mods">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Moderators</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
          <p>
	    <font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	    Moderators controleren individuele
            forums. Ze kunnen berichten in hun forumes bewerken, verwijderen en opschonen.
			Als je een vraag hebt over een bepaald forum dan moet je die aan de forum moderator te stellen.</p>
          <p>Beheerders en forum moderators hebben het recht om ieder bericht dat geen zinvolle bijdrage levert
	         te blokkeren of te verwijderen. Er zijn nog steeds veel gebruikers die gebruik maken van
            28.8 en 56k modems die niet de tijd hebben om door zich door nutteloze en onzinnige discussies te
            worstelen. </p>
          <p>Iemand die alleen berichten plaats om bovenaan te komen in de statistieken van de <?php echo $sitename?> Forums
          of berichten plaatst uit verveling loopt het risico dat zijn/haar berichten geblokkeerd of verwijderd worden of dat
          hij/zij als gebruiker wordt verwijderd. </p>
          <p>Probeer de bewoording van het onderwerp van je bericht in lijn te houden met onderwerp van de gaande
          discussie. Onderwerpen als "Kijk hier eens naar!" en "~~\\Dit MOET je zien!//~~" trekken gebruikers naar onderwerpen
          die ze waarschijnlijk helemaal niet willen lezen.</font></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<p align="left"><a name="profile">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Je profiel aanpassen</b></font></a></p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Je kunt eenvoudig de informatie aanpassen die in je profiel si opgeslagen bij je registratie.
	Dit doe je door te klikken op de &quot;profiel&quot; link die je bovenaan iedere pagina kan vinden.
	Identificeer je door je gebruikersnaam en wachtwoord in te type of door je eerst aan te melden
	en alle informatie in je profiel verschijnt op het scherm.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="prefs">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Voorkeuren aanpassen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Als een geregisteerde forum gebruiker kan je je gebruikersnaam tot
	een jaar lang laten onthouden door je browser.  Door dit te doen hebben we
	de mogelijkheid om je bezoeken aan het forum te volgen en daardoor jou de
	mogeijkheid bieden de weergave/vormgeving van het forum aan te passen door
	een van de thema's die de beheerders beschikbaar stellen te selecteren.
	<br>*NOOT: Om thema's te kunnen gebruiken MOETEN cookies ingeschakeld zijn.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="cookies">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Cookies</b></font></a></td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Dit bulletin board maakt gebruik van cookies om de volgende informatie op te slaan:
        het tijdstip van je laatste bezoek aan de forums, je gebruikersnaam
		en een uniek sessienummer als je je aanmeld. Deze cookies worden door je browser
		opgeslagen. Als je browser geen cookies ondersteund of als je cookies niet hebt
		ingeschakeld in je browser werken geen van deze functies.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="edit">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Je berichten bewerken</b></font></a>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Je kan op ieder moment je berichten bewerken. Zoek je bericht op in de discussie waar je het bericht geplaatst
	hebt. Onder de lijn onder je bericht bevind zich een "Edit" icoon. Klik op dit icoon en bewerk je bericht.
	Alleen jij, de forum moderator en de beheerder kunnen je bericht bewerken.
	Bovendien heb je de eerste 30 minuten na het plaatsen van je bericht de mogelijkheid om het bericht weer
	te verwijderen. Na de 30 minuten kan je bericht alleen nog door de forum moderator of de beheerder worden
	verwijderd.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td><a name="signature">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Onderschrift toevoegen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Je kunt een onderschrift voor al je berichten gebruiken.
	Als je klikt op de profiel link bovenaan een pagina kun je je profiel aanpassen, inclusief je
	standaard onderschrift. Als je eenmaal een onderschrift hebt opgelsagen, kun je ervoor kiezen om
	deze toe te voegen aan je bericht door de optie &quot;Onderschrift gebruiken&quot; aan te vinken als je het bericht maakt.
	De beheerder kan er echter voor kiezen om deze functionaliteit uit te schakelen. Als dit het geval is zal
	de optie &quot;include onderschrift&quot; niet zichtbaar zijn als je een bericht schrijft. Zelfs niet als je een
	onderschrift hebt opgeslagen in je profiel. Je kunt op ieder moment je onderschrift aanpassen door dit in je
	profiel te wijzigen.
	<p>Noot: Je kunt HTML of <a href="#bbcode">BB Code</a> gebruiken als de beheerder deze opties ingeschakeld heeft.
	    </font>
        </p>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="attach">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Bestanden toevoegen</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Om veiligheidsredenen is het niet mogelijk om bestanden toe te voegen aan berichten.
	Je kan echter wel tekst knippen en in je tekst plakken of HTML en/of  BB Code gebruiken (indien ingeschakeld)
	om hyperlinks temaken naar documenten elders.
	</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="search">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Zoeken naar specifieke berichten</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Je kunt zoeken naar specifieke berichten op basis van een of meerdere woorden, een gebruikersnaam,
	een datum en/of een bepaald forum. Klik hiervoor op de link &quot;search&quot;, op de meeste pagina's
	bovenaan te vinden.</font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="pw">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Wachtwoord kwijtgeraakt</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	In het geval dat je je wachtwoord kwijt raakt kun je klikken op de link &quot;Ik weet mijn wachtwoord niet meer!?&quot;
	Deze link is te vinden op de pagina waar je een bericht maakt en op de pagina waar je je aanmeld. Deze link neemt je
	mee naar een pagina waar je je gebruikersnaam en emailadres kan invullen. Het systeem stuurt je dan een nieuw
	willekeurig gegenereerd wachtwoord naar het emailadres in je profiel, ervanuitgaande dat je het juiste emailadres hebt opgegeven.</FONT>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="notify">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Email Notificatie</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	Als je een nieuw onderwerp begint, heb je de mogelijkheid aan te geven of je een email wilt
	ontvangen als er iemand een reactie op je bericht plaatst.
	Vink de optie &quot;Waarschuwen via mail als er een reactie komt op dit bericht&quot; aan bij het maken
	van het nieuwe onderwerp als je van deze mogelijkheid gebruik wilt maken. </font>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="ranks">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Wat zijn de rangen voor de <?php echo $sitename?> Forums?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	De <?php echo $sitename?> Forums hebben methoden vastgesteld om hun gebruikers
	te onderscheiden naar activiteit door het aantal geplaatste berichten.</p>
	<br>
	De huidigen rangen zijn als volgt:<br>

	<?php
	$sql = "SELECT * FROM ranks WHERE rank_special = 0";
	if(!$r = mysql_query($sql, $db)) {
	echo "Error connecting to the database";
	include('page_tail.'.$phpEx);
	exit();
	}
	?>
	<br><TABLE BORDER="0" WIDTH="<?php echo $TableWidth?>" CELLPADDING="1" CELLSPACING="0" ALIGN="CENTER" VALIGN="TOP"><TR><TD BGCOLOR="<?php echo $table_bgcolor?>">
	<TABLE BORDER="0" CELLPADDING="1" CELLSPACING="1" WIDTH="100%">
	<TR BGCOLOR="<?php echo $color1?>" ALIGN="CENTER">
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Rang Titel&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Minimum Aantal Berichten&nbsp;</font></TD>
	<TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Maximum Aantal Berichten&nbsp;</font></TD>
        <TD><font face="<?php echo $FontFace?>" size="<?php echo $FontSize2?>" color="<?php echo $textcolor?>">&nbsp;Rang Afbeelding&nbsp;</font></TD>
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
	echo "<TD COLSPAN=\"4\">No Ranks in the database</TD>";
	echo "</TR>";
	}
	?>
	</TABLE></TABLE></font>
	<br>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	De beheerder heeft ook de mogelijkheid om naar believen speciale rangen toe te kennen aan een gebruikers.
	De bovenstaande tabel bevat deze speciale rangen niet.
	</font>
        </td>
    </tr>
    <tr bgcolor="<?php echo $color1?>">
        <td>
	<a name="rednumbers">
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>Waarom
	</font>
	<font color="#FF0033" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b>"branden"</b>
	</font>
	<font color="<?php echo $textcolor?>" size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>">
	<b> sommige berichten in het forum overzicht?</b></font></a>
	</td>
    </tr>
    <tr bgcolor="<?php echo $color2?>">
        <td>
	<font size="<?php echo $FontSize2?>" face="<?php echo $FontFace?>" color="<?php echo $textcolor?>">
	"Brandende" iconen geven aan dat er <?php echo $hot_threshold?> of meer berichten in die discussie
	geplaatst zijn.
	</font></p>
        </td>
    </tr>
</table>
</TABLE>
</center>
</div>

<?php
include('page_tail.'.$phpEx);
?>
