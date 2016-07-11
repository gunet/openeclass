<?php

// Message file for language de
// Generated 2016-07-11 12:00:26

$langCourseAccessHelp = "
<ul>
<li>".$course_access_icons[COURSE_OPEN]."<b> Open Course</b>. The course is publicly accessible without need of user authentication or login.</li>
<li>".$course_access_icons[COURSE_REGISTRATION]."<b> Registration required</b>.  Users with an account in the platform can register to the course. It is possible to specify a course password to further limit access to only users who have this extra password.</li>
<li>".$course_access_icons[COURSE_CLOSED]."<b> Closed Course</b>. The course is accessible only to users already registered to it. The course administrator can add or remove users from the course.</li>
<li>".$course_access_icons[COURSE_INACTIVE]."<b> Inactive course</b>. Access is allowed <b>only</b> to course teachers - administrators.</li>
</ul>
";
$langWikiSyntaxHelp = "
<h4>Basic syntax</h4>
<p>Creating wiki pages and links between them</p>
<p><strong>Wiki words</strong>: Wiki words are words written like <em>WikiWord</em>. To create a wiki page or a link to a wiki page, edit an existing one and add the title of the page in WikiWord syntax, for example <em>MyPage</em>, then save the page. The Wiki Word <em>MyPage</em> will automatically be replaced with a link to the Wiki page <em>MyPage</em>&nbsp;</p>
<p><strong>Wiki links</strong>: Wiki links are like Hyperlinks (see below) apart from the fact that they do not contain any protocol scheme (like <em>http://</em> or <em>ftp://</em>) and are automatically recognized as links to Wiki pages. To create a new page or create a link to an existing one using Wiki links, edit a page and add <code>[page title]</code> or <code>[name of link|title of page]</code> to its content. You can also use this syntax to change the text of a WikiWord link : <code>[name of link|WikiWord]</code>.</p>
<ul>Hyperlinks
<li><code>[url]</code>, <code>[name|url]</code>, <code>[name|url|language]</code> or <code>[name|url|language|title]</code>.&nbsp;;</li></ul>
<ul>Image inclusion
<li><code>((url|alternate text))</code>, <code>((url|alternate text|position))</code> or <code>((url|alternate text|position|long description))</code>. <br />The position argument can take the following values: L (left), R (right) or C (center). &nbsp;
You can also use the syntax of Hyperlinks. For example <code>[title|image.gif]</code>. This syntax is deprecated, so it is better to consider using the preceding one &nbsp;</li></ul>
<ul>Link to an image
<li>same as Hyperlinks but put a 1 as the fifth argument to avoid uploading an image and get a hyperlink to the image instead. For example <code>[image|image.gif||0]</code> will display a link to image.gif instead of displaying the image itself</li></ul>
<p>Layout</p>
<ul>
<li><strong>Italic</strong>: enclose your text in two straight single quotes <code>\'\'text\'\'</code>&nbsp;</li>
<li><strong>Bold</strong>: enclose your text in three straight single quotes <code>\'\'\'text\'\'\'</code>&nbsp;</li>
<li><strong>Underline</strong>: enclose your text in two underscores <code>__text__</code>&nbsp;</li>
<li><strong>Stroke</strong>: enclose your text in two minus symbols <code>--text--</code>&nbsp;</li>
<li><strong>Title</strong>: <code>!!!</code>, <code>!!</code>, <code>!</code> respectively for titles, sub-titles and sub-sub-titles&nbsp;</li>
<li>List</li>
line starting with <code>*</code> (unordered list) or <code>#</code> (ordered list). You can mix lists (<code>*#*</code>) to create multi-level lists&nbsp;
<li>Paragraph</li>
Separate paragraphs with one or more new lines&nbsp;
</ul>
<h4>Advanced syntax</h4>
<ul>
<li>Footnote</li>
<code>$$ footnote text$$</code>&nbsp;
<li>Preformatted text</li>
Begin each line of preformatted text with a blank space &nbsp;
<li>Cite block</li>
<code>&gt;</code> or <code>;:</code> before each line &nbsp;
<li>Horizontal line</li>
<code>----</code>&nbsp;
<li>Forced line break</li>
<code>%%%</code>&nbsp;
<li>Acronym</li>
<code>??acronym??</code> or <code>??acronym|definition??</code>&nbsp;
<li>Inline cite</li>
<code>{{cite}}</code>, <code>{{cite|language}}</code> or <code>{{cite|language|url}}</code>&nbsp;
</ul>
</ul>
<p> The 'Inactivation' / 'Activation' link moves the Wiki system from active to inactive and vice versa tools.</p>";
$langWindowClose = "Fenster schließen";
$langHDefault = 'Hilfe ist nicht verfügbar';
$langDefaultContent = '<p>Es ist kein Hilfe-Text verfügbar für die Seite der Plattform, die Sie 
gerade besucht haben.</p>';
$langPHPMathPublisher = "<p><u>Unterstützung für mathematische Symbole:</u>
<br />
Für die Eingliederung von mathematischen Symbolen in Ihren Texten, 
können Sie die <a href='http://wiki.openeclass.org/doku.php?id=mathp'>Befehle</a> 
(Symbole) verwenden, die PhpMathPublisher bereitstellt.<br />
Genauer gesagt, benutzen Sie ihren Text-Editor um Ihren Inhalt einzugeben.
Falls in dem von Ihnen eingegebenen Text mathematische Symbole vorkommen und diese dargestellt 
werden sollen, dann:
<ol>
  <li>Klicken Sie auf das icon 'Umschalten auf HTML Quellkode'.</li>
  <li>Importieren Sie ihre mathematische Symbole zwischen den tags &lt;m>.....&lt;/m></li>
</ol>
<p>Um die Befehle zu finden, welche den von Ihnen gewünschten konkreten Symbolen entsprechen, 
konsultieren Sie bitte (<a href='../../manuals/PhpMathPublisherHelp.pdf' target='_blank'>hier</a>).<br />
Um z.B. die Quadratwurzel von a zu erhalten, geben Sie ein &lt;m>sqrt{a}&lt;/m></p>";
$langHFor = "Foren";
$langHFor_student = $langHFor ;
$langForContent = "<p>Das Forum ist ein Werkzeug zur asynchronen schriftlichen Kommunikation. Während e-mail 
Dialoge nur zwischen zwei Partnern zuläßt, erlauben die Foren öffentliche Diskussionen. Aus 
technischer Sicht wird zur Benutzung eines Forums nur ein Web-Browser benötigt.</p>
<p>Zur Organisation der Foren, klicken Sie auf 'Administrieren'.
Die Diskussionen sind auf folgende Weise in Gruppen und Untergruppen organisiert:</p>
<p><b>Kategorie > Forum > Themengebiet > Antworten</b></p>

<p>Um die Diskussionen ihrer Studenten zu strukturieren, ist es notwendig von vorneherein 
Kategorien und Foren zu organisieren und die Erzeugung von Themenbereichen und Antworten den 
Studenten zu überlassen. Die Open eClass Foren beinhalten ein Forum und einen Themenbereich als 
Musterbeispiel.</p>

<p>Sie sollten zuerst den Muster-Themenbereich löschen und das Forum entsprechend umbenennen. 
Danach können Sie weitere Foren erzeugen entweder nach Gruppen oder nach Themen organisiert, je 
nachdem wie es Ihren Ausbildungsaktivitäten entspricht.</p><p>Vermischen Sie nicht die Kategorien und 
die Foren und vergessen Sie nicht, dass eine leere Kategorie (ohne Foren) in der Sicht der Studenten 
nicht angezeigt werden wird.</p>

<p>Die Beschreibung eines Forums kann die Liste der Mitglieder, dessen Zweck, eine Tätigkeit oder 
ein Thema usw beinhalten.</p><p>Der Link 'Deaktiviereng' / 'Aktivierung' verschiebt die Foren 
von den aktiven zu den inaktiven Werkzeugen und umgekehrt.</p>";
$langHDoc = "Dokumente";
$langHDoc_student = $langHDoc;
$langDocContent = "<p>Das Subsystem 'Dokumente'ist in seiner Funktionalität ähnlich dem 
Dateimanager ihres Personalcomputers. Sie können Dateien jeglichen Typs hochladen (HTML, Word, 
Powerpoint, Excel, Acrobat, Flash, Quicktime, usw). Ihre einzige Restriktion besteht darin, dass 
die Studenten das entsprechende Anwendungsprogramm installiert haben müssen, um diese Dateien zu 
lesen. Einige Dateitypen können Viren enthalten, deshalb liegt die Verantwortung bei Ihnen, keine 
mit Viren befallenen Dateien hochzuladen. Es wird empfohlen, Ihre Dateien mit einem Virenschutzprogramm 
zu kontrollieren, bevor Sie diese Dateien hochladen.</p>

<p>Die Dokumente werden in alphabetischer Reihenfolge dargestellt.
<br><b>Ratschlag:</b> Falls Sie diese mit unterschiedlicher Reihenfolge darstellen möchten, 
nummerieren Sie diese: 01, 02, 03...</p>

<p>Es kann ausgeführt werden:</p>
<h4>Hochladen einer Datei</h4>
<ul>
  <li>Klicken Sie auf den Link 'Zum Server hochladen'. 
  Wählen Sie daraufhin die Schaltfläche 'Browse' in dem dargestelltem Formular, damit Sie die Datei 
  auf ihrem Rechner auswählen (auf der rechten Seite des Bildschirms).</li>
<li>Schließen Sie das Hochladen ab durch Klicken auf die Schaltfläche 'Hochladen'.</li>
</ul>
<h4>Löschen einer Datei (oder Verzeichnisses)</h4>
<ul>
<li>Klicken Sie auf das Icon 'Löschen' (<img src='../../template/classic/img/delete.png' width=10 height=10 align=baseline>)</li>
</ul>
<h4>Verschieben einer Datei (oder Verzeichnisses)</h4>
    <ul>
      <li>Klicken Sie auf das Icon 'Verschieben' (<img src='../../template/classic/img/move.png'
 width=10 height=10 align=basename>)
        </li>
      <li>Wählen Sie das Verzeichnis aus, in das Sie die Datei (oder das Verzeichnis) verschieben möchten.
	  (Bemerkung: die Bezeichnung 'Wurzelverzeichnis' bedeutet, dass Sie nicht über diese Ebene im 
	  Dateibaum des Servers hinaus navigieren können).</li>
      <li>Bestätigen Sie durch Klicken auf 'Verschieben'.</li>
    </ul> 

<h4>Umbenennen einer Datei (oder eines Verzeichnisses)</h4>
<ul>
  <li>Klicken Sie auf das Icon 'Umbenennen' (<img src='../../template/classic/img/edit.png' width=10 height=10
 align=baseline>)</li>
  <li>Geben Sie den neuen Namen in das Feld (oben links) ein</li>
  <li>Bestätgen Sie durch Klicken auf die Schaltfläche 'Umbenennen'</li>
</ul>
    <h4>Hinzufügen oder Ändern eines Kommentars in einer Datei (oder in einem Verzeichnis)</h4>
    <ul>
  <li>Klicken Sie auf das Icon 'Bemerkung' (<img src='../../template/classic/img/information.png' width=16
 height=16 align=baseline>)
    in der Spalte </li>
      <li>Geben Sie einen neuen Kommentar in das entsprechende Eingabefeld ein (oben rechts).</li>
      </ul>

	  <h4>Umwandlung einer Datei (oder Verzeichnisses) in unsichtbar für die Studenten</h4>
    <ul>
  <li>Klicken Sie auf das Icon 'Sichtbar/Unsichtbar' (<img src='../../template/classic/img/visible.png' width=10 height=10 align=baseline>)
</li>
   <li>Nach dieser Aktion wird die Datei (oder das Verzeichnis) existieren, aber für die Studenten 
   nicht sichtbar sein.</li>
  <li>Um diese wieder sichtbar zu machen, klicken Sie auf das Icon 'Sichtbar/Unsichtbar' (<img src='../../template/classic/img/invisible.png' width=14 height=10 align=baseline>
  </li>
    </ul>
    <hr>
    <p>Sie können Ihre Inhalte in Verzeichnissen organisieren. Für diesen Zweck:</p>
    <h4><b>Erzeugen Sie ein Verzeichnis</b></h4>
    <ul>
      <li>Klicken Sie auf den Link 'Erzeugen eines Verzeichnisses'</li>
      <li>Geben Sie den Namen des neuen Verzeichnisses in das entsprechende Eingabefeld ein</li>
      <li>Klicken Sie danach auf 'Verzeichnis erzeugen'.</li>
    </ul>
    <hr>
    <p>Sie können Ihren verfügbaren Speicherplatz einsehen</p>
    <h4><b>Übersicht des Speicherplatzes</b></h4>
    <ul>
      <li>Klicken Sie auf den Link 'Übersicht des Speicherplatzes'</li>
      <li>Es erscheint eine Tabelle mit dem benutzten Speicherplatz, der prozentualen Benutzung 
	  und dem gesamten verfügbaren Speicherplatz.</li>
    </ul>    
      </p>
    <p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Dokumente von den aktiven zu den 
	inaktiven Werkzeugen und umgekehrt.</p>
";
$langDoc_studentContent = "<p>Die Dokumente bilden denjenigen Platz, wo das Unterrichtsmaterial des 
Kurses abgespeichert, organisiert und präsentiert wird. In diesem Subsystem können Sie die 
vorhandenen Texte, Skripte, Präsentationen, Bilder, Diagramme usw zum Kurs finden mit Hilfe eines 
Systems von Verzeichnissen und Unterverzeichnissen.</p>";
$langHUser = "Benutzerverwaltung";
$langUserContent = "<p><b>Rollen</b></p>
<p>Die Rollen der Benutzer auf der Plattform sind vollkommen unabhängig vom Computersystem, auf 
dem die Plattform ausgeführt wird. Das bedeutet, dass diese Rollen überhaupt keine Zugriffsrechte 
auf dem Betriebssystem ermöglichen. </p>
<hr>
<p><b>Rechte des Administrators</b></p>
<p>Die Administrator-Zugriffsrechte entsprechen der technischen Bevollmächtigung die Inhalte und 
die Struktur des Kurses zu verändern.</p>
<p>Damit Sie einem Ihrer Assistenten erlauben, einen Kurs mitzuverwalten, brauchen Sie diesen nur 
am Kurs zu registrieren. Danach klicken Sie auf 'Hinzufügen' (unter 'Rechte des 
Administrators').</p><hr>
<p><b>Hinzufügen eines Benutzers</b></p>
<p>Damit Sie einen Benutzer zu ihren Kurs hinzufügen können, klicken Sie auf den Link 
'Hinzufügen eines Benutzers', füllen danach die Eingabefelder aus und klicken danach auf 'Suchen'. 
Nach dem Auffinden des Benutzers in der Plattform klicken Sie auf den Link 
'Registrierung des Benutzers am Kurs'.</p>";
$langHGuest = "Gast Benutzer";
$langGuestContent = "<p>Durch Auswahl von 'Gast Benutzer hinzufügen'haben Sie die Möglichkeit, 
einen Gast Benutzer zu erzeugen, dessen Daten Sie den Benutzern mitteilen werden, die am Kurs 
registriert sind (oder der Plattform). Ein Gast Benutzer hat die Möglichkeit die Startseite 
desjenigen Kurses zu sehen, an dem er registriert ist und alle Werkzeuge, welche aktiviert sind. Aber er 
kann keine Aktionen ausführen wie Studienarbeiten hochladen.</p>
";
$langHQuestionnaire = "Fragebogen";
$langHQuestionnaire_student = $langHQuestionnaire ;
$langQuestionnaireContent = "<p>Das Werkzeug der Fragebögen erlaubt Ihnen die Erzeugung und 
Verwaltung von Fragebögen.</p>
<p>Um einen Fragebogen zu erzeugen, klicken Sie auf den Link 'Fragebogen erzeugen'. Geben Sie in 
dem folgenden Formular seinen Titel und den Zeitraum an, in welchem der Fragebogen aktiv sein wird. 
Wählen Sie die von Ihnen gewünschte Art der Fragen aus durch Klicken auf die entsprechenden 
Schaltflächen ('neue Frage mit mehrfacher Auswahl' und 'neue Frage mit Ausfüllen einer Lücke'). 
Nachdem Sie die Fragen und die zugehörigen Antworten vervollständigt haben, klicken Sie auf 
'Fragebogen erzeugen'.
</p>
<p>Die Ergebnisse können Sie sich auf der Seite der Verwaltung der Fragebögen anschauen.</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Fragebögen von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>";
$langQuestionnaire_studentContent = "<p>Dieses Subsystem stellt die Möglichkeit zur Teilnahme an 
Fragebögen und Umfragen zur Verfügung, die der verantwortliche Dozent des Kurses erzeugt 
hat.</p><p> Für die Teilnahme an einem Kurs reicht es aus, den Link 'Teilnahme' auszuwählen.</p>";
$langHExercise = "Übungen";
$langHExercise_student = $langHExercise ;
$langExerciseContent = "<p>Das Werkzeug der Übungen erlaubt Ihnen die Erzeugung von Übungen, die soviele Fragen enthalten 
werden, wie Sie es wünschen.<br>
<br>Es gibt unterschiedliche Typen von Antworten auf ihre Fragen:<br>
<ul>
  <li>Mehrfacher Auswahl (einzige Antwort)</li>
  <li>Mehrfacher Auswahl (mehrere Antworten)</li>
  <li>Zusammenpassen</li>
  <li>Ausfüllen von Lücken</li>
</ul></p>
<p>Eine Übung beinhaltet nur einen Typ von Fragen</p>
<hr>
<p><b>Übung erzeugen</b><br><br>
Damit Sie eine Übung erzeugen:
<ol>
  <li>Zuerst muss das Subsystem der Übungen für diesen Kurs aktiviert sein</li>
  <li>Klicken Sie auf den Link &quot;Neue Übung&quot;.</li>
  <li> Geben Sie den Namen der Übung ein, sowie eine (optionale) Beschreibung.</li>
  <li> Sie können zwischen 2 Typen von Aufgaben wählen :
    <ul>
      <li>Fragen nur auf einer Seite</li>
      <li>Eine Frage pro Seite</li>
    </ul>
  </li>
  <li>Wählen Sie das Datum und den Zeitpunkt des Starts der Übung aus (zB 1977-06-29 12:00:00). 
  Vor diesem Zeitpunkt werden die Studenten nicht an der Übung teilnehmen können.</li>
  <li>Wählen Sie das Datum und den Zeitpunkt des Endes der Übung aus (π.χ. 1977-06-29 12:00:00). 
  Danach werden die Studenten nicht mehr an der Übung teilnehmen können.</li>
  <li>Legen Sie die zeitliche Restriktion fest, welche die Teilnehmer einhalten müssen, also 
  wieviele Minuten Sie zur Verfügung haben müssen um alle Fragen zu beantworten (0 für gar keine 
  Einschränkung). </li>
  <li>Geben Sie die Anzahl der erlaubten Wiederholungen ein, also wie oft jemand an der Übung 
  teilnehmen kann (0 für uneingeschränkte Anzahl von Wiederholungen).</li>
</ol>
<br>
<br></p>
<p>Danach speichern Sie die Übung. Sie werden zur Verwaltung von Fragen weitergeleitet werden 
für diese Ubung.</p>
<hr>
<p><b>Frage hinzufügen</b></p>
<p>Sie können eine Frage hinzufügen zur Übung, die Sie vorher erzeugt haben. Sie können eine Frage 
hinzufügen oder löschen durch Klicken auf die entsprechende Schaltfläche. Die Beschreibung sowie 
ein Bild ist optional.</p>
<hr>
<p><b>Mehrfacher Auswahl</b></p>
<p>
Damit Sie eine Frage mehrfacher Auswahl erzeugen:<br><br>
<ul>
  <li>Erzeugen Sie Antworten auf Ihre Frage. Sie können eine Antwort hinzufügen oder löschen durch 
  Anklicken der entsprechenden Schaltfläche.</li>
  <li>Haken Sie den linken Knopf (Schaltfläche) ab für die richtige Antwort</li>
  <li>Fügen Sie (falls Sie das möchten) einen Kommentar hinzu. Der Student/Lerner wird diesen Kommentar 
  erst sehen, nachdem er die Frage beantwortet hat.</li>
  <li>Geben Sie eine Gewichtung (Note) für jede Antwort. Das Gewicht (Note) kann eine jede positive 
  oder negative Zahl sein oder die Null.</li>
  <li>Speichern Sie ihre Antworten ab</li>
</ul></p>
<hr>
<p><b>Ausfüllen von Lücken</b></p>
<p>Sie können einen Lückentext erzeugen. Der Sinn besteht darin, dass die Studenten die fehlenden 
Wörter finden sollen.<br><br>
Um ein Wort aus dem Text zu entfernen, so dass eine Lücke entsteht, setzen Sie diese zwischen Eckige 
Klammern [wie diese Wörter].<br><br>
Von dem Zeitpunkt an, wo der Text eingegeben worden ist und die Lücken festgelegt worden sind, 
können Sie einen Kommentar hinzufügen, welcher für den Studenten erst sichtbar sein wird, nachdem 
er die Frage beantwortet hat.<br><br>
Speichern Sie ihren Text ab und Sie werden zum nächsten Schritt voranschreiten, wo Sie die Möglichkeit 
haben werden eine Gewichtung für jede Lücke festzulegen. Als Beispiel können Sie bei einer Frage 
mit 5 Lücken mit Bestnote die 10 für jede Lücke eine Benotung von 2 ansetzen.</p>
<hr>
<p><b>Zusammenpassen</b></p>
<p>Sie können eine Frage erzeugen, bei der der Student die Elemente aus 2 Mengen kombinieren 
muss.<br><br> Sie können auch von den Studenten verlangen, Elemente mit einer gewissen Reihenfolge zu 
sortieren.<br><br> Zuerst definieren Sie die Auswahlmöglichkeiten, zwischen denen die Studenten 
die richtige Antwort auswählen müssen. Danach definieren Sie die Fragen, welche mit den 
Auswahlmöglichkeiten verbunden werden müssen. Zum Schluss entsprechen Sie mittels des Menus die jeweiligen Elemente der ersten Menge mit denjenigen 
der zweiten Menge.<br><br>
Bemerkung: Einige Elemente aus der ersten Menge können auf das selbe Element der zweiten Menge 
verweisen. Geben Sie eine Gewichtung für jede richtige Entsprechung an und speichern Sie ihre 
Antwort ab.<br><br>
<hr>
<p><b>Ändern der Übung</b></p>
<p>Um eine Übung zu ändern, führen Sie ähnliche Aktionen aus wie bei der Erzeugung.
Klicken Sie auf das Bild <img src=\"../../template/classic/img/edit.png\" border=\"0\" align=\"absmiddle\"> 
neben der Übung, um diese zu ändern.</p>
<hr>
<p><b>Löschen der Übung</b></p>
<p>Um eine Übung zu löschen, klicken Sie auf das Bild <img src=\"../../template/classic/img/delete.png\" border=\"0\" align=\"absmiddle\"> 
neben der Übung.</p>
<hr>
<p><b>Aktivierung der Übung</b></p>
<p>Damit Ihre Übung von den Studenten benutzt werden kann, müssen Sie diese Übung aktivieren durch 
Klicken auf das Bild <img src=\"../../template/classic/img/invisible.png\" border=\"0\" align=\"absmiddle\"> 
neben der Übung.</p>
<hr>
<p><b>Ausprobieren der Übung</b></p>
<p>Sie können Ihre Übung durch klicken auf deren Namen ausprobieren.</p>
<hr>
<p><b>Zufällige Übungen</b></p>
<p>Zum Zeitpunkt der Erzeugung / Änderung einer Übung können Sie festlegen, ob die Übungen in 
zufälliger Reihenfolge erscheinen sollen.<br><br>
Das bedeutet, dass durch Aktivierung dieser Option die Fragen jeweils in einer anderen Reihenfolge 
auftreten werden, wenn diese Übung von den Studenten ausgeführt werden wird.<br><br>
Wenn Sie eine große Anzahl von Fragen haben, können sie bestimmen, dass nur eine bestimmte Anzahl 
von Fragen in zufälliger Reihenfolge auftreten werden.</p>
<hr>
<p><b>Verfügbare Fragen</b></p>
<p>Wenn Sie ein Übung löschen, dann werden die Fragen nicht in der Datenbank gelöscht und können 
somit in einer neuen Übung wiederverwendet werden.<br><br>
Auf diese Art können Sie die selben Fragen in mehreren Übungen verwenden.<br><br>
Im Normalfall werden alle Fragen des Kurses dargestellt. Sie können die zu einer Übung gehörenden 
Fragen sehen durch Auswahl der Übung im Menu &quot;Filtern&quot;.<br><br>
Die &quot;Fragen ohne Antwort&quot; sind diejenigen, die zu keiner Übung gehören.</p>
<p><b>Ergebnisse</b></p>
<p>Sie können sich die Ergebnisse ansehen in der Form wie Sie möchten.</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Übungen von den aktiven zu den inaktiven 
Werkzeugen und umgekehrt.</p>
";
$langExercise_studentContent = "<p>Dieses Subsystem beinhaltet einen Mechanismus zur Verwaltung von Übungen zur Selbstbewertung 
(Eigenevaluierung, Selbsttest-Aufgaben) für die Auszubildenden (Studenten), welche vom verantwortlichen 
Dozentes des Kurses erstellt worden sind. Die grundlegende Zielsetzung ist die intensive Auseinandersetzung 
der Studenten mit dem Stoff des Kurses. Bei einigen Übungen kann es eine zusätzliche zeitliche Beschränkung geben oder eine Beschränkung der 
Teilnehmerzahl.</p><p> Damit Sie eine Übung zur Selbstbewertung bearbeiten, wählen Sie diese 
durch Anklicken auf Ihren Namen aus.</p>";
$langHWork = "Studienarbeiten";
$langHWork_student = $langHWork ;
$langWorkContent = "<p>Das Subsystem der Studienarbeiten stellt ein integriertes System zur 
Erzeugung / zum Vortrag / der Benotung von Studienarbeiten dar.</p>
<p>Als Dozent können Sie eine Studienarbeit erzeugen durch Klicken auf <b>\"Studienarbeit erzeugen\"</b>.
Füllen Sie den Titel der Studienarbeit aus, legen Sie den Abgabetermin fest und geben sie evtl. 
(optional) einen Kommentar ein.</p>
<p>Wenn die Erzeugung Studienarbeit vollendet worden ist, vergessen Sie nicht diese zu aktivieren 
durch Anklicken auf das Icon <img src=\"../../template/classic/img/invisible.png\" border=\"0\" align=\"absmiddle\">. 
Die Studienarbeit wird von den Studenten sichtbar sein und zur Verfügung stehen, nur wenn 
Sie diese aktivieren. Sie können diese Studienarbeit jederzeit verbessern durch Anklicken auf das Icon 
<img src=\"../../template/classic/img/edit.png\" border=\"0\" align=\"middle\"> 
oder diese Löschen durch Anklicken auf das Icon 
<img src=\"../../template/classic/img/delete.png\" border=\"0\" align=\"middle\">.
Durch Anklicken des Titels der Studienarbeit sehen Sie die potentiell von den Studenten 
abgegebenen Beiträge dazu. Als Detailangaben zu den Studenten werden die Matrikelnummer (Studenten ID), 
das Datum der Abgabe und die entsprechende Datei angezeigt. Durch Anklicken auf \"Herunterladen aller Beiträge in Form von .zip \" werden Sie alle Dateien 
\"herunterladen\", welche die Studenten abgegeben haben (in komprimierter Form von .zip) und somit 
repräsentieren alle diese Dateien die von Ihnen gestellte Sutdienarbeit. 
Um die Studienarbeit zu benoten füllen Sie die entsprechende Note neben den Namen des Studenten aus 
und klicken Sie auf die Schaltfläche <b>\"Einreichung der Benotung\"</b>. Der Student wird seine 
Note sehen, sobald er auf den Titel der Studienarbeit klicken wird.</p>
<p>Der Student kann alle Studienarbeiten sehen, die vom Dozenten vergeben worden sind. Die Liste der 
Studienarbeiten enthält (außer dem Titel der Studienarbeit) die Abgabefrist, evtl die Benotung 
durch den Dozenten und eine Anzeige ob er einen Beitrag zur Studienarbeit abgegeben hat oder nicht. 
Er kann seinen Beitrag zur Studienarbeit abschicken durch Anklicken auf den entsprechenden Titel. Es 
ist zu beachten, dass ein Student seinen Beitrag zur Studienarbeit nicht nach dem Ablaufen der Abgabefrist 
abgeben darf.
Falls er einen Beitrag schon \"hochgeladen\" hatte und noch einen Beitrag \"hochladen\" möchte, 
dann wird der erste Beitrag gelöscht werden und durch den neuen ersetzt werden.
</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Studienarbeiten von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>
";
$langWork_studentContent = "<p>Das Subsystem Studienarbeiten des Kurses ist ein sehr nützliches 
Werkzeug des elektronischen Kurses, da es die elektronische Abgabe und Benotung der Studienarbeiten 
des Kurses unterstützt.</p><p>Insbesondere bietet es den registrierten Studenten die Möglichkeit ihre Studienarbeiten bis zur 
Abgabefrist elektronisch auf die Plattform hochzuladen. Danach können sie ihre Noten sehen, nachdem 
der Dozent diese benotet hat.</p> 
$langPHPMathPublisher";
$langHGroup = "Benutzergruppen";
$langHGroup_student = $langHGroup;
$langGroupContent = "<p>Dieses Werkzeug erlaubt das Erzeugen von Benutzergruppen. Während des Erzeugens 
('Erzeugen einer neuen Benutzergruppe') sind die Gruppen leer. Die Gruppen können auf unterschiedliche 
Weise mit Mitgliedern bevölkert (ergänzt) werden:
<ul><li>Automatisch ('Ergänzung der Benutzergruppen'),</li>
<li>durch den verantwortlichen des Kurses ('Korrektur der Benutzergruppen'),</li>
<li>durch Selbstregistrierung der Studenten ('Einstellungen Ändern': Die Studenten dürfen sich eintragen...).</li>
</ul>
<p>Diese drei Arten können kombiniert werden. Sie können zB von den Studenten verlangen, dass diese 
sich selbst registrieren. Später beobachten Sie, dass einige Studenten sich nicht in einer Gruppe 
registriert haben und somit wählen Sie die automatische Registrierung in der Gruppe aus.
Sie können übrigens auch die Zusammensetzung der Gruppen ändern, sowohl vor als auch nach der 
automatischen Registrierung oder der Selbstregistrierung der Benutzer.</p>
<p>Die Registrierung in Gruppen (ob automatisch oder nicht) funktioniert nur, falls es schon 
registrierte Studenten an diesem Kurs gibt. Die Registrierung in einer Gruppe unterscheidet sich 
von der Registrierung des Benutzers am Kurs. Die am Kurs registrierten Studenten werden im Werkzeug 
'Benutzer' dargestellt.</p>
<hr noshade size=1>
<p><b>Erzeugen von Gruppen</b></p>
<p>Zum Erzeugen von neuen Gruppen wählen Sie 'Erzeugen einer neuen Benutzergruppe' aus und geben Sie 
an, wieviele Gruppen erzeugt werden sollen. Die Festlegung der maximalen Anzahl von Gruppenmitgliedern 
ist optional, aber es wird empfohlen eine konkrete Zahl einzugeben (sonst werden sich eine unbegrenzte 
Anzahl von Studenten an diesen Gruppen registrieren lassen können).</p>
<hr noshade size=1>
<p><b>Ergänzung von Benutzergruppen</b></p>
<p>Damit Sie neue Benutzergruppen mit Benutzern ergänzen, wählen Sie 'Ergänzung von Benutzergruppen' 
und legen Sie fest, welche Benutzer welcher Gruppe angehören werden.</p>
<hr noshade size=1>
<p><b>Löschen aller Benutzergruppen</b></p>
<p>Damit Sie alle Benutzergruppen zusammen mit den Benutzern des Kurses löschen, wählen Sie 
'Löschen aller Benutzergruppen'. Es werden sowohl die Benutzer als auch die Gruppen gelöscht. Nur 
die Datei mit den Diskussionen der Gruppe (falls vorhanden) bleibt übrig, für zukünftigen 
Gebrauch.</p>
<hr noshade size=1>
<p><b>Entleerung aller Benutzergruppen</b></p>
<p>Damit Sie alle Benutzer des Kurses löschen, aber gleichzeitig die Benutzergruppen vorhanden 
bleiben und leer sind, wählen Sie 'Entleerung aller Benutzergruppen' aus. Die Datei mit den 
Diskussionen der Gruppe (falls vorhanden) bleibt übrig, für zukünftigen Gebrauch.</p>
<hr noshade size=1>
<p><b>Einstellungen der Gruppen</b></p>
<p>Sie können die Einstellungen festlegen, die für alle Gruppen gelten sollen.
<p><b>Einstellungen der Benutzergruppen</b>:
<p>Sie können leere Gruppen erzeugen und die Registrierung von Studenten zulassen. Falls Sie eine 
maximale Anzahl von Gruppenmitgliedern festgelegt haben, dann lassen die vollständigen Gruppen 
keine neuen Mitglieder zu. Diese Methode ist hilfreich für den Fall, dass Sie beim Erzeugen der 
Gruppen nicht die vollständigen Listen der Studenten kennen.</p>
<p><b>Werkzeuge</b>:</p>
<p>Jede Gruppe hat entweder ein Diskussionsforum (öffentlich oder privat) oder einen Dokumentenbereich 
(ein System zur Verwaltung von Dateien, welche von den Mitgliedern geteilt werden) oder (öfter) 
beides.</p>
<hr noshade size=1>
<p><b>Korrektur der Gruppen</b></p>
<p>Nachdem die Gruppen erzeugt wurden, erscheint im unteren Teil der Seite eine Liste der Gruppen 
mit mehreren Detailinformationen und Funktionen.
Wählen Sie:
<ul><li><b>Korrektur</b> damit Sie den Namen der Gruppe, die Beschreibung, den Dozenten und die 
Liste der Mitglieder ändern.</li>
<li><b>Löschen</b> um eine Gruppe zu löschen.</li></ul>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Benutzergruppen von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>
";
$langGroup_studentContent = "<p>Eine Benutzergruppe ist eine Menge von registrierten Benutzern des 
Kurses, welche das gleiche Diskussionsforum und den selben Dokumentenbereich teilen</p>";
$langHAgenda = "Agenda";
$langHAgenda_student = $langHAgenda ;
$langAgendaContent = "<p>Falls Sie ein Ereignis in Ihren Terminkalender (Agenda) einfügen möchten, 
klicken Sie auf den Link 'Hinzufügen eines Ereignisses'. Geben Sie das Datum des Ereignisses, den 
Titel und die Detailinformationen in dem darauf folgenden Formular ein. Klicken Sie danach auf die 
Schaltfläche 'Hinzufügen/Ändern'. Dadurch wird das Ereignis in den Terminkalender eingefügt.</p><p>
Als nächstes haben Sie die Möglichkeit einige der Parameter des Ereignisses zu verändern (falls von 
Ihnen gewünscht) indem Sie das Icon 'Ändern' wählen. Sie können ein Ereignis aus dem Terminkalender 
löschen durch Auswahl des Icons 'Löschen' oder das Ereignis sichtbar (oder unsichtbar) machen durch 
Auswahl des Icons 'Sichtbar/Unsichtbar'.</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt den Terminkalender des Kurses von den aktiven 
zu den inaktiven Werkzeugen und umgekehrt.</p>
$langPHPMathPublisher";
$langAgenda_studentContent = '<p>Das Subsystem Terminkalender erlaubt Ihnen die wichtigen Ereignisse 
des Kurses in ihrer chronologischen Reihenfolge zu verfolgen (Vorlesungen, Meetings, Benotungen usw). 
Sie können die Reihenfolge der Darstellung ändern, mit der die Ereignisse ausgegeben werden 
(jüngstes - ältestes), durch Anklicken des Links "Umkehrung der Reihenfolge bei der Darstellung" 
oben rechts.</p>';
$langHLink = "Links";
$langHLink_student = $langHLink;
$langLinkContent = "<p>Sie können Web-Links auf Web-Seiten im Internet (oder auf Ihre lokalen Web-Seiten) hinzufügen.</p><p>
Damit Sie einen Link hinzufügen, wählen Sie 'Hinzufügen von Link'. Geben Sie den URL, den Titel 
und eine Beschreibung des Link ein. Wählen Sie die Kategorie, in die der Link hinzugefügt werden soll 
und klicken Sie danach auf die Schaltfläche 'Hinzufügen'.</p> <p>Wählen Sie 'Kategorie hinzufügen', 
damit Sie eine neue Kategorie von Links erzeugen, so dass bestimmte Links gruppiert werden können.
Geben Sie den Namen und die Beschreibung der Kategorie ein. Klicken Sie danach auf die Schaltfläche 
'Hinzufügen'. Sie können zusätzlich den Namen oder die Beschreibung des Links ändern durch Auswahl 
von 'Ändern' oder den Link löschen durch Auswahl von 'Löschen'.
Durch Auswahl von 'Kategorie Löschen' löschen Sie die Kategorie und alle darin enthaltenen Links.</p>
<p>Letztlich gibt es die Auswahl 'Anzeigen' zum Anzeigen der in einer Kategorie enthaltenen Links 
und die Auswahl 'Verstecken' zum Verbergen der Links.</p>
<p>Falls Kategorien definiert worden sind, dann legen Sie beim Hinzufügen eines Links fest, zu 
welcher Kategorie er gehören wird.</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Links von den aktiven zu den inaktiven 
Werkzeugen und umgekehrt.</p>
";
$langLink_studentContent = "<p>Das Subsystem Links bietet den Zugang zu hilfreichen Quellen aus dem 
Internet, die in Kategorien gruppiert sind und relevant zum Kurs sind. Diese Links wurden vom 
verantwortlichen Dozenten des Kurses ausgewählt. Jeder Link verfügt über einen Titel und eine 
Beschreibung mit zusätzlichen Informationen.</p>";
$langHAnnounce = "Ankündigungen";
$langHAnnounce_student = $langHAnnounce ;
$langAnnounceContent = "<p>Sie können Ankündigungen zur Seite des Kurses hinzufügen durch Anklicken 
des Links 'Ankündigung hinzufügen'. In dem darauf folgenden Formular geben Sie den Titel und den 
Hauptteil der Ankündigung ein. Danach klicken Sie auf 'Hinzufügen'. Falls sie möchten, dass die 
Ankündigung per e-mail an die Studenten, die an diesem Kurs registriert sind, geschickt wird, dann 
wählen Sie 'Abschicken (per e-mail) der Ankündigung an alle registrierten Benutzer'.</p>
<p>Außerdem können Sie den Inhalt der Ankündigung ändern durch Auswahl des entsprechenden Icons 
für 'Ändern' und eine Ankündigung löschen durch Auswahl des Icons für 'Löschen'. Diese Icons 
befinden sich in der Spalte 'Werkzeuge'.</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Ankündigungen von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>
$langPHPMathPublisher";
$langAnnounce_studentContent = '<p>Das Subsystem Ankündigungen unterstützt die Informierung der 
registrierten Benutzer über kursrelevante Angelegenheiten. Gleichzeitig haben die verantwortlichen 
Dozenten des Kurses die Möglichkeit, die Ankündigungen per e-mail an die am Kurs registrierten 
Studenten zu schicken.</p><p>
Sie müssen in Ihrem Profil eine gültige e-mail Adresse angegeben haben, damit sie die Ankündigungen 
per e-mail erhalten können. ("Ändern meines Profils" im Portfolio des Benutzers).
</p>';
$langHProfile = "Ändern der persönlichen Daten";
$langProfileContent = "<p>Sie haben die Möglichkeit einige ihrer persönlichen Daten, die Sie auf 
der Plattform verwenden, zu verändern.</p>
<li>Im Fall eines falschen Eintrags können Sie den Vornamen, Nachnamen und die e-mail Adresse 
ändern.</li>
<li>Falls Sie es wünschen, können Sie den Benutzernamen und das Passwort ändern.</li>
<li>Zum Schluß klicken Sie auf die Schaltfläche 'Ändern', damit die Änderungen in der Datenbank 
abgespeichert werden.</li>";
$langHModule = "Einen externen Link hinzufügen";
$langModuleContent = "<p>Falls Sie einen Link von der Startseite des Kurses auf andere (im Internet 
vorhandene) Webseiten (oder auf Ihre persönlichen Webseiten) hinzufügen möchten, dann geben Sie den 
Link und den Titel des Links ein und klicken danach auf die Schaltfläche 'Hinzufügen'. Die von Ihnen 
hinzugefügten Webseiten auf der Startseite können deaktiviert und gelöscht werden, dagegen die 
integrierten Werkzeuge können zwar deaktiviert, jedoch nicht gelöscht werden.</p>";
$langHcourseTools = "Aktivierung der Werkzeuge";
$langcourseToolsContent = "<p>Der Dozent kann die Subsysteme des Kurses aktivieren und deaktivieren. 
In den beiden Spalten wird der Zustand jedes Subsystems dargestellet, also ob es aktiv ist oder 
nicht.</p>
<p>Um den Zustand eines Subsystems zu ändern, klicken Sie auf das entsprechende Subsystem und klicken 
dann auf die Schaltfläche '>>'. Sie können mehrere Subsysteme auf einmal von der einen zur anderen 
Spalte verschieben, indem Sie gleichzeitig die Taste CTRL gedrückt halten und auf das Subsystem klicken. 
Zum Schluß klicken Sie auf die Schaltfläche 'Änderungen abschicken', damit Ihre Änderungen abgespeichert 
werden.</p>
<p>Außerdem kann eine fertige Webseite hochgeladen und zur Plattform hinzugefügt werden durch Auswahl 
des Links 'Webseite hochladen'. Falls es einen externen Weblink mit für die Plattform relevanten 
Inhalten gibt, dann kann dieser festgelegt werden mit Hilfe des Links 
'Hinzufügen eines externen Links im linken Menu'.</p>";
$langHInfocours = "Administration des Kurses";
$langHConference = "Telearbeit";
$langHConference_student = $langHConference ;
$langConferenceContent = "<p>Sie können Nachrichten mit allen am Kurs registrierten Benutzern 
austauschen indem Sie ihre Nachricht eintippen und danach auf die Schaltfläche '>>' klicken.
Weiterhin können Sie alle Nachrichten während des Live-Gesprächs (Chat) löschen durch Auswahl von 
'Entleerung'. Durch Auswahl von 'Speichern' werden alle von Ihnen ausgetauschten Nachrichten im 
Subsystem Dokumente gespeichert.</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Telearbeit von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>";
$langConference_studentContent = '<p>Das Subsystem "Telearbeit"(conference) beinhaltet die 
Möglichkeit, Textnachrichten auszutauschen (chat). Der Auszubildende (Student) hat die Möglichkeit, 
mit allen am Kurs registrierten Benutzern Nachrichten auszutauschen, indem er die Nachricht eintippt 
und danach auf die Schaltfläche ">>" klickt.</p>';
$langHVideo = "Video";
$langHVideo_student = $langHVideo ;
$langVideoContent = '<p>Ein Teil der Lehrmaterialien kann Audiovisuelles Material sein. Dieses 
Lehrmaterial kann auf zwei Arten verteilt werden. Die erste Möglichkeit besteht im Herunterladen 
der Dateien vom entfernten Rechner auf den lokalen Rechner des Benutzers und das Abspielen der lokalen 
Datei nach Vollendung des Herunterladens. Als zweite Möglichkeit kann das Audiovisuelle Material von einem 
Streaming-Server bereitgestellt werden. Der Vorteil ist, dass das Material unmittelbar und ohne Verzögerung 
am entfernten Rechner wiedergegeben (abgespielt) wird. Das Subsystem Video bietet die Möglichkeit, 
die Plattform mit einem Streaming-Server zu verbinden.</p>
<p>
Sie können Videodateien (vom Typ mpeg, avi usw) auf die Plattform hochladen. Wählen Sie "Video hinzufügen" 
und geben danach den Namen der Datei ein oder klicken Sie auf die Schaltfläche "Lokalisierung", um nach 
der Datei zu suchen. Geben Sie zusätzlich in den entsprechenden Feldern den Titel des Videos ein und 
(falls erwünscht) eine Beschreibung ein. Klicken Sie danach auf die Schaltfläche "Hinzufügen", damit 
das Hochladen auf die Plattform ausgeführt wird.</p>
<p>
Sie haben die Möglichkeit Links auf Videomittschnitte von Vorlesungen von Kursen in die Webseiten des 
Kurses hinzuzufügen. Wählen Sie "Videolink hinzufügen" und geben Sie die Adresse des VOD (Video on 
Demand) Servers (auf dem sich die Videodatei des Kurses befindet) in das Feld "URL" ein. Geben Sie 
danach den Titel und die Beschreibung ein und klicken schließlich auf "Hinzufügen". Sie können 
einige der Parameter ändern durch Auswahl von "Ändern" oder den Link bzw die Datei löschen durch 
Auswahl von "Löschen". Außerdem werden durch "Alles Löschen" alle Links auf Videomittschnitte 
von Kursen gelöscht und auch alle Dateien, die auf der Seite des Kurses sind.</p>
<p>
Wenn das System mit einem Streaming-Server verbunden ist, dann ist diese Verbindung für den Dozenten 
transparent und er braucht nichts weiteres zu veranlassen, damit die Dateien mit dem Audiovisuellen 
Material vom Streaming-Server zur Verfügung gestellt werden. Sie müssen beachten, dass dieses Material 
vom Streaming Server auch außerhalb der Open eClass Plattform verfügbar sein wird, falls jemand den 
direkten Link auf die Datei auf dem Server kennt.
</p>
<p>Der Link "Deaktivierung" / "Aktivierung" verschiebt das Video Subsystem von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>
';
$langVideo_studentContent = "<p>Es handelt sich um ein Subsystem zur Bereitstellung von Audiovisuellen 
Lehrmaterialien. Es gibt Videodateien und Links auf externe Videodateien, die auf einem Video On Demand 
(VOD) Server abgespeichert sind.
</p>";
$langHCoursedescription = "Beschreibung des Kurses";
$langHCoursedescription_student = $langHCoursedescription ;
$langCoursedescriptionContent = "<p>Sie haben die Möglichkeit Informationen zum Kurs hinzuzufügen 
durch Auswahl von 'Erzeugen und Korrektur'. Die Kategorien sind 'Inhalt des Kurses', 
'Unterrichtsaktivitäten', 'Hilfen', 'Personal', 'Arten von Benotungen / Prüfungen', 
'zusätzliche Informationen'. Sie können eine Kategorie hinzufügen, indem Sie diese aus der Liste der 
vorhandenen Kategorien auswählen und durch Klicken auf die Schaltfläche 'Hinzufügen'.</p><p> 
Danach wird die der gewählten Kategorie entsprechende Beschreibung eingegeben. 
Danach können diese Änderungen entweder 
durch 'Hinzufügen' endgültig übernommen werden oder die Beschreibung der Kategorie kann rückgängig 
gemacht werden durch Auswahl von 'Abbrechen und zurück'. Zur Korrektur einer Beschreibung einer 
Kategorie kann das Icon 'Ändern' verwendet werden, oder zum Löschen einer Beschreibung einer Kategorie 
kann das Icon 'Löschen' verwendet werden, welches sich auf der selben Zeile mit der zu bearbeitenden 
Kategorie befindet. <p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Beschreibung des Kurses 
von den aktiven zu den inaktiven Werkzeugen und umgekehrt.</p>$langPHPMathPublisher";
$langCoursedescription_studentContent = "<p>Falls der verantwortliche Dozent eine Beschreibung des 
Kurses erzeugt hat, dann werden Sie in diesem Subsystem hilfreiche Informationen finden bzgl der 
Identität des Kurses, der Ziele und der Lehrmaterialien, der Art der Benotung und Prüfung, des 
unterstützenden Lehrmaterials und der Unterrichtsaktivitäten und jeglicher weiterer Informationen, 
die der Dozent für wichtig erachtet.</p>";
$langHPath = "Hilfe - Lernkurve";
$langHPath_student = $langHPath ;
$langPathContent = "Das Werkzeug Lernkurve hat vier Funktionen:
<ul>
<li>Lernkurve erzeugen</li>
<li>Lernkurve vom SCORM Format oder IMS importieren</li>
<li>Lernkurve exportieren ins Format Scorm 2004 oder 1.2</li>
<li>Beobachtung (Verfolgung) des Fortschritts der Lernenden (Studenten) in der Lernkurve</li>
</ul>

<p><b>Was ist eine Lernkurve ?</b></p>
<p>Die Lernkurve ist eine Abfolge von Lernschritten, die in Abschnitten enthalten sind. Sie kann 
entweder auf Inhalt (einem Inhaltsverzeichnis ähnelnd) basiert sein oder auf Aktivitäten basiert 
sein, so dass sie einer Agenda (Terminkalender) oder einem Programm ähnelt, welches vorgibt, was 
der Student zu tun hat, um eine konkrete Wissens(-quelle) zu verstehen oder (an diesem) Wissen und 
Fertigkeiten zu üben und ausgebildet zu werden.
</p>

<p>Außer dass Sie strukturiert sein kann, kann eine Lernkurve zusätzlich eine spezifische Reihenfolge 
haben. Das bedeutet, dass einige Schritte Vorbedingungen für die unmittelbar folgenden Schritte sind 
(\"Sie können nicht zu Schritt 2 gelangen vor Schritt 1\"). Die Reihenfolge kann nur angedeutet sein 
(die Schritte werden einer nach dem anderen angezeigt).</p>

<p><b>Wie können Sie ihre eigene Lernkurve erzeugen ?</b></p>

<p>Im ersten Schritt begeben Sie sich in den Bereich Liste der Lernkurven. In der Hauptseite der 
Liste der Lernkurven befindet sich diesbezüglich ein bestimmter Link. Dort können Sie soviele Lernkurven 
erzeugen, wie Sie es wünschen, indem Sie auf <i>Neue Lernkurve erzeugen</i> klicken. Auf diese Weise 
werden leere Lernkurven erzeugt, bis Sie denen jeweils Lerneinheiten und Schritte hinzufügen.</p>

<p><b>Welche sind die Schritte für diese Kurven ? (Welche Objekte können hinzugefügt werden ?)</b></p>

<p>Einige der Werkzeuge, Aktionen und der Inhalte von Open eClass, die Sie möglicherweise als hilfreich 
erachten und ihrer Kurve hinzugefügt werden können:</p>

<ul>
<li>separate Dokumente (Texte, Bilder, Dokumente wie von Office ...)</li>
<li>Label / Etiketten</li>
<li>Links</li>
<li>Übungen von Open eClass</li>
<li>Beschreibung des Kurses</li>
</ul>

<p><b>Weitere Charakteristiken der Lernkurve</b></p>
<p>Von den Studenten kann verlangt werden, Ihre Kurve mit einer bestimmten Reihenfolge zu befolgen. 
Das bedeutet zB, dass die Studenten auf die Übung 2 nicht zugreifen dürfen, wenn diese nicht vorher 
Dokument 1 gelesen haben. Alle Objekte besitzen einen Zustand: vollendet oder nicht vollendet, so 
dass der Fortschritt der Studenten immer verfügbar ist über das spezielle Werkzeug <i>Beobachtung 
der Lernkurven</i>.</p>

<p>Falls Sie den aktuellen Titel eines Schrittes ändern möchten, kann der neue Titel erscheinen, ohne 
dass der vorherige Titel davon betroffen wird. Wenn Sie also test8.doc als 'Endgültige Version' der Prüfung 
in der Kurve darstellen möchten, dann brauchen sie diese Datei nicht umzubenennen, sondern es reicht 
aus diesen anderen Titel in der Kurve anzugeben. Ebenfalls ist es vorzuziehen, denjenigen Links mit 
einem langen Namen einen neuen Titel zu vergeben. 
.</p>
<br>

<p><b>Was ist eine Lernkurve im SCORM Format oder IMS und wie können Sie diese importieren ?</b></p>

<p>Das Werkzeug Lernkurve erlaubt Ihnen Unterrichtsmaterialien, die kompatibel zu den Formaten SCORM 
und IMS sind, hochzuladen und zu importieren.</p>

<p>Das SCORM (<i>Sharable Content Object Reference Model</i>) ist ein international anerkanntes 
Format, dem die bedeutendsten Organisationen folgen, die im Bereich asynchrones e-Learning tätig sind, 
wie zB: NETg, Macromedia, Microsoft, Skillsoft, usw. Dieses Format agiert auf 3 Ebenen:</p>

<ul>
<li><b>Wirtschaft</b>: Das SCORM Format erlaubt ganzen Kursen oder kleineren Einheiten von Inhalten 
die Wiederverwendung in unterschiedlichen eLearning Plattformen (Learning Management Systems - LMS), 
durch die Trennung des Inhalts und seiner Struktur (Kontext),</li>
<li><b>Pädagogik</b>: Das SCORM Format integriert den Begriff der Vorbedingungen oder der 
<i>Reihenfolge</i> (<i>zB </i>\"Sie können auf Kapitel 2 nicht zugreifen, solange Sie Übung 1 nicht 
erfolgreich beendet haben\"),</li>
<li><b>Technologie</b>: Das SCORM Format erzeugt ein Inhaltsverzeichis, welches eine zusätzliche 
Abstraktionsebene darstellt, die unabhängig vom Inhalt und der jeweiligen eLearning Plattform ist. 
Es hilft bei dem Austausch (Kommunikation) zwischen Inhalt und eLearning Plattform. Diese Kommunikation 
besteht hauptsächlich aus <i>Zeigern</i> (\"wo genau befindet sich Johannes ?\"), <i>Benotung</i> 
(\"Mit welcher Note hat Johannes den Test bestanden ?\") und <i>Zeit</i> (\"Wie lange hat sich 
Johann in Kapitel 1 aufgehalten ?\").</li>
</ul>

<p><b>Wie erzeugen Sie eine zu SCORM kompatible Lernkurve ?</b></p>

<p>Die eingängigste Methode besteht darin, das Werkzeug 'Lernkurve erzeugen' von Open eClass zu 
benutzen und danach diese Lernkurve zu exportieren durch Klicken auf das entsprechende Icon. Aber 
vielleicht möchten Sie zu SCORM kompatible Unterrichtsmaterialien lokal auf ihrem Rechner erzeugen 
und diese danach in das Open eClass Werkzeug Lernkurve einfügen. In diesem Fall empfehlen wir Ihnen, 
ein spezialisiertes Werkzeug wie zB Lectora&reg; oder Reload&reg; zu benutzen.</p> 

<p><b>Hilfreiche Links</b></p>

<ul>
<li>Adlnet: die zuständige Organisation zur Standardisierung des Scorm Formats, <a
href=\"http://www.adlnet.org/\">http://www.adlnet.org</a></li>
<li>Reload: ein Open Source Werkzeug zum Bearbeiten und zur Darstellung von Scorm Inhalten, <a
href=\"http://www.reload.ac.uk/\">http://www.reload.ac.uk</a></li>
<li>Lectora: ein Werkzeug zum Berbeiten und Veröffentlichen von Scorm Inhalten, <a
href=\"http://www.trivantis.com/\">http://www.trivantis.com</a></li>
</ul>

<p><b>Bemerkung:</b></p>

<p>Der Bereich Lernkurve listet alle <i>mittels Open eClass selbst konstruierten</i> Lernkurven auf 
und alle <i>zu SCORM kompatible</i> importierte Lernkurven. 

<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt die Lernkurve von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>
";
$langPath_studentContent = "<p>Der Student kann mittels des Subsystems zu unterschiedlichen Lernkurven 
navigieren und die von Dozenten definierten Schritte mit einer bestimmten Reihenfolge befolgen. In 
den Fällen, wo dies erforderlich ist, beobachtet das System den Fortschritt, die Zeit und die Benotung 
des Studenten in den unterschiedlichen Schritten der jeweiligen Lernkurve.</p>";
$langHDropbox = "Bereich zum Austausch von Dateien";
$langHDropbox_student = $langHDropbox ;
$langDropboxContent = "<p>Der Bereich zum Austausch von Dateien ist ein Werkzeug zum Austausch von 
Dateien zwischen den Dozenten und den Studenten. Sie können jegliche Arten von Dateien austauschen 
(zB Word, Excel, PDF usw).</p>
<p>Es gibt zwei Verzeichnisse im Bereich zum Austausch von Dateien. 
Im Verzeichnis <b>Eingehende Dateien</b> werden die von anderen Benutzern der Plattform empfangenen 
Dateien angezeigt, zusammen mit zusätzlichen Informationen bzgl der Datei, wie der Benutzername, die 
Dateigröße und das Datum des Empfangens. Im Verzeichnis <b>Gesendete Dateien</b> werden diejenigen 
Dateien dargestellt, die Sie an andere Benutzer der Plattform geschickt haben zusammen mit den 
entsprechenden Informationen.</p>
<p>Um eine Datei zu löschen, klicken Sie auf das Icon (<img src='../../template/classic/img/delete.png' width=10 height=10 align=baseline>), 
welches sich auf der selben Zeile mit der zu löschenden Datei befindet. Beachten Sie, dass beim Löschen 
einer Datei diese nicht in der Datenbank der Plattform gelöscht wird, sondern nur im Verzeichnis.</p>
<p>Zum Senden einer Datei zu einem Benutzer, wählen Sie die Datei auf Ihrem Rechner aus durch Klicken 
auf 'Datei hochladen'. Sie können optional eine Beschreibung hinzufügen. Wählen Sie aus dem 
Verzeichnis der Benutzer den Empfänger der Datei und Klicken Sie auf 'Absenden'. Falls Sie die Datei 
an mehrere Benutzer schicken möchten, wählen Sie die gewünschten Empfänger durch Klicken auf den 
Namen des Empfängers aus, wobei Sie gleichzeitig die Taste <b>CTRL (Control)</b> gedrückt halten.
</p>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt den Austausch von Dateien von den aktiven zu 
den inaktiven Werkzeugen und umgekehrt.</p>
";
$langDropbox_studentContent = '<p>Der Bereich zum Austausch von Dateien ist ein Werkzeug zum Austausch 
von Dateien zwischen Auszubildenden (Studenten) und des verantwortlichen Ausbilders (Dozent). Sie 
können jegliche Arten von Dateien austauschen (Text, Bilder oder Präsentationen).</p>
<p>Um speziell eine Datei zu senden, wählen Sie den Link "Datei hochladen".
</p>';
$langHUsage = "Statistik der Benutzung";
$langUsageContent = "<p>Dieses Subsystem bietet dem Dozenten die Möglichkeit Statistiken bzgl des 
Kurses zu betrachten. Diese Statistiken werden in der Form von graphischen Darstellungen oder 
Listen dargestellt.</p>
<p><strong>Kategorien von Statistiken</strong></p>
<ul>
<li>Statistiken der Benutzung</li>
<li>Bevorzugung von Subsystemen</li>
<li>Besuche der Benutzer zum Kurs</li>
<li>Darstellung alter Statistiken</li>
</ul>
<p>Die Statistiken können gruppiert werden nach Anzahl von Besuchen oder nach der zeitlichen Dauer 
der Besuche. Außerdem kann ausgewählt werden, für welche Subsysteme Statistiken und die zeitliche 
Dauer benötigt werden.</p>
<p>Die Statistiken der Bevorzugung von Subsystemen können gruppiert werden nach der Anzahl der 
Besuche oder der zeitlichen Dauer der Besuche. Außerdem kann ausgewählt werden, für welche Benutzer 
Statistiken benötigt werden.</p>
<p>Die Statistiken der Besuche von Benutzern zum Kurs können gruppiert werden nach den Benutzern, 
für welche Statistiken benötigt werden.</p>
<p>Die Darstellung alter Statistiken kann gruppiert werden nach der Anzahl von Besuchen oder der 
zeitlichen Dauer von Besuchen. Außerdem kann ausgewählt werden für welche Subsysteme Statistiken 
benötigt werden sowie deren zeitliche Dauer.</p>
";
$langHCreateCourse = "Kurs erzeugen";
$langCreateCourseContent = "<p>Der Assistent zum Erzeugen von Kursen ist ein sehr wichtiges Werkzeug 
der Plattform, da der Benutzer-Dozent mit dessen hilfe neue kurse erzeugen kann.</p><p> Der Assistent 
besteht aus 3 Schritten. Das Ausfüllen mit den erforderlichen Informationen der mit Stern (*) 
gekennzeichneten Eingabefelder ist zwingend erforderlich. Unter jedem Eingabefeld befindet sich ein 
Wert als Beispiel, um den Benutzer beim Ausfüllen zu unterstützen.</p><p>Im Fall der Eingabe eines 
falschen Wertes informiert das System den Benutzer und fordert Ihn auf, diesen zu beheben, so dass 
er mit dem nächsten Schritt fortfahren kann.</p>";
$langHWiki = "Wiki-System";
$langHWiki_student = $langHWiki ;
$langWikiContent = "<p>Damit Sie ein neues Wiki erzeugen,</p>
<ul>
<li>klicken Sie auf den Link 'ein neues Wiki erzeugen'. Geben Sie danach die Eigenschaften des Wiki ein:</li>
<li><b>Titel des Wiki</b> : wählen Sie einen Titel für das Wiki</li>
<li><b>Beschreibung des Wiki</b> : wählen Sie eine Beschreibung für das Wiki </li>
<li><b>Verwaltung der Zugangskontrolle</b> : legen Sie die Zugangskontrolle für das Wiki fest, indem 
Sie das Kästchen (siehe weiter unten) selektieren / deselektieren </li>
</ul>
<p>Um ein Wiki einzufügen, klicken Sie auf den Titel des Wiki in der Liste.</p>
<p>Um die Eigenschaften des Wiki zu ändern, klicken Sie auf das Icon <img src='../../template/classic/img/edit.png' align='absmiddle' border='0'>.</p>

<p>Um das Wiki zu löschen, klicken sie auf das Icon <img src='../../template/classic/img/delete.png' align='absmiddle' border='0'></p>
<p>Um die Liste der zuletzt geänderten Seiten zu sehen, klicken Sie auf das Icon <img src='../../template/classic/img/history.png' align='absmiddle' border='0'></p>

<p>Benutzen Sie die Icons, um Ihren Text zu formatieren. Beachten Sie, dass die Möglichkeit besteht 
einen Link zu erzeugen (durch Auswahl und Markierung ihres Textes und Anklicken des Icon 
<img src='../../modules/wiki/toolbar/bt_link.png' align='absmiddle' border='0'>) auf eine neue Seite 
des Wiki, welche Sie danach erzeugen werden.
Alternativ dazu wird ein in eckige Klammern enthaltener Text automatisch in einen internen Link 
auf die entsprechende Seite umgewandelt (zB bei dem Satz \"Die [Lösung des Problems] ist\" wird der 
Text zwischen den Eckigen Klammern in einen Link umgewandelt).

<h4>Syntax der Befehle</h4>
<p>Erzeugen von Wiki Seiten und Links zwischen den Seiten</p>
<p><strong>Wörter des Wiki</strong>: Die Wörter des Wiki sind Wörter, die geschrieben sind wie 
<em>Wort</em>. Um eine Wiki Seite zu erzeugen oder einen Link auf eine Wiki Seite, korrigieren 
Sie eine vorhandene und fügen den Titel der Seite hinzu gemäß der Syntax. Als Beispiel nehmen Sie 
<em>Meine Seite</em>, und danach speichern Sie diese. Das Wort <em>Meine Seite</em> wird automatisch 
ersetzt werden von einem Link auf die Wiki Seite <em>Meine Seite</em>&nbsp;</p>
<p><strong>Wiki Links</strong>: Die Wiki Links sind wie Hyperlinks (siehe weiter unten) außer dass 
diese sich nicht auf ein Protokoll beziehen (wie zB <em>http://</em> oder <em>ftp://</em>) und 
automatisch umgewandelt werden in Links auf Wiki Seiten. Damit eine neue Seite oder ein Link auf 
eine vorhandene Seite erzeugt wird, korrigieren Sie die Seite und fügen 
<code>[Titel der Seite]</code> oder <code>[Name des Links|titel der Seite]</code> im Inhalt hinzu. 
Sie können folgende Syntax benutzen, um den Namen des Wiki Links zu verändern: 
<code>[Name des Links|Wort]</code>.</p>
<ul>Hyperlinks
<li><code>[url]</code>, <code>[Name|url]</code>, <code>[Name|url|Sprache]</code> oder 
<code>[Name|url|Sprache|Titel]</code>.&nbsp;</li></ul>
<ul>Bild einfügen
<li><code>((url|alternativer Text))</code>, <code>((url|alternativer Text|Position))</code> oder 
<code>((url|alternativer Text|Position|Beschreibung))</code>. 
<br />Das Positions-Argument kann folgende Werte enthalten : L (links), R (rechts) oder 
C (Mittelpunkt).&nbsp;
Außerdem können Sie die Syntax der Hyperlinks verwenden, zB <code>[titel|image.gif]</code>. Die 
Syntax befindet sich dabei abgeschafft zu werden, deshalb ist es besser die vorherige Syntax 
zu verwenden &nbsp;</li></ul>
<ul>Link auf ein Bild
<li>wie bei den Hyperlinks, aber setzen Sie eine 1 als fünftes Argument, um das Laden des Bildes 
zu vermeiden, und somit einen Hyperlink auf das Bild zu erhalten. Als Beispiel wird 
<code>[Bild|image.gif||0]</code> einen Link auf dieses Bild image.gif darstellen anstatt das Bild 
selbst darzustellen.</li></ul>

<p>Layout</p>
<ul>
<li><strong>Kursivschrift</strong>: umgeben Sie ihren Text mit einfachen Anführungszeichen <code>''Text''</code>&nbsp;</li>
<li><strong>Fett gedruckt</strong>: umgeben Sie ihren Text mit 3 einfachen Anführungszeichen <code>'''Text'''</code>&nbsp;</li>
<li><strong>Unterstrichen</strong>: umgeben Sie ihren Text mit 2 Unterstrichen <code>__Text__</code>&nbsp;</li>
<li><strong>Durchgestrichen</strong>: umgeben Sie ihren Text mit 2 Minuszeichen <code>--Text--</code>&nbsp;</li>
<li><strong>Titel</strong>: <code>!!!</code>, <code>!!</code>, <code>!</code> für Titel, 
Unter-Titel und Unter-Unter-Titel&nbsp;</li>
<li>Liste</li>
Die Zeile muß mit <code>*</code> (nicht geordnete Liste) oder <code>#</code> beginnen (geordnete 
Liste). Sie können die Listen vermischen (<code>*#*</code>), damit Sie Listen mehrerer Ebenen 
erzeugen.&nbsp;
<li>Paragraph</li>
Teilen Sie die Paragraphen durch eine oder mehrere Zeilen&nbsp;
</ul>

<h4>Fortgeschrittene Syntax</h4>
<ul>
<li>Fußnote</li>
<code>$$ Text der Fußnote$$</code>&nbsp;
<li>Text mit vorgefertigter Formatierung</li>
Beginnen Sie jede Zeile mit einem Leerzeichen &nbsp;
<li>Block eines Zitats</li>
<code>&gt;</code> oder <code>;:</code> vor jeder Zeile &nbsp;
<li>Horizontale Linie</li>
<code>----</code>&nbsp;
<li>Gezwungener Zeilenumbruch</li>
<code>%%%</code>&nbsp;
<li>Akronym</li>
<code>??Akronym??</code> oder <code>??Akronym|Definition??</code>&nbsp;
<li>Eingebettetes Zitat</li>
<code>{{Zitat}}</code>, <code>{{Zitat|Sprache}}</code> ή <code>{{Zitat|Sprache|url}}</code>&nbsp;
</ul>
</ul>
<p>Der Link 'Deaktivierung' / 'Aktivierung' verschiebt das Wiki-System von den aktiven zu den 
inaktiven Werkzeugen und umgekehrt.</p>";
$langWiki_studentContent = "<p>Das Wiki-System ist ein Kollaborationswerkzeug zum Lernen, welches in 
der aktuellen Version der Open eClass Plattform integriert ist. Es bietet den Teilnehmern des 
Kurses (Dozenten und Studenten) die Möglichkeit, gemeinsam den Inhalt von unterschiedlichen Texten 
zu bearbeiten.</p>";
$langHGlossary = "Glossary";
$langHGlossary_student = $langHGlossary;
$langGlossaryContent = "<p>
<ul>
  <li>To add a glossary term for your course click on the 'Add new term' option. In the 'Add new term' form, type the term, the term definition, optionally a reference URL or/and some additional notes. Click on 'Submit' to complete with the process.</li>
  <li>To add a new category (for term classification) click on 'Add category'. Type the category name and optionally a description in the next page and click on 'Submit'.</li>
 <li>Extra configuration settings for the glossary can be found at the 'Config settings' page.</li>
 <li>Selection of the 'Terms in csv form' allows you to select the appropriate encoding and download all terms in csv format.</li>
 </ul>
</p>
 <p>
 <ul>
 <li>Click on the <img src='$themeimg/edit.png' width=16 height=16> icon to edit a term.</li>
 <li>Click on <img src='$themeimg/delete.png' width=16 height=16> to delete a term.</li>
</ul>
</p>";
$langGlossary_studentContent = "
<p>Glossary displays terms and / or definitions for various keywords in course.</p>
<p>Glossary terms are displayed by hovering the mouse over a term. Also, sometimes, glossary term includes a hyperlink.</p>";
$langHEBook = "E-Book";
$langHEBook_student = $langHEBook;
$langEBookContent = "
<p>The 'E-Book' is a set of hypertex content that 'simulates' a book (print version). Basically an electronic book is a flexible structure that apart from text in digital format, can additionally be enriched with multimedia content such as images, videos, external links, etc. Moreover, this module allows structuring of the book contents into sections - subsections. The presentation of content is made through a list box.</p>
<hr>
<h2>&nbsp;&nbsp;E-Book prerequisites (Step 1)</h2>
<p>To create a new e-book, you must create some html pages (files) that will be the e-book contents. But you must keep in mind the following rules (specifications):</p>
<ol>
  <li>Every html page must include one of the e-book subsections (this relates with the e-book navigation).</li>
  <li>The title of every html page should include the exact title of the included subsection (the titles will be used by the system for the creation of navigation links within the e-book).</li>
  <li>All html files along with their corresponding pictures and css files must be stored in a zip file.</li>
</ol>
<hr>
<h2>&nbsp;&nbsp;E-Book creation (Step 2)</h2>

<ol>
  <li>Enter the platform as course administrator. </li>
  <li>Select the <strong>'E-Book'</strong> module</li>
  <li>Click on <strong>'Create'</strong>'
    <ul>

      <li>Fill in the <strong>Title</strong> of the E-Book and</li>
      <li>Browse your computer and locate the zip file with the html pages/files you have created.</li>
    </ul>
  </li>
    <li>Click on <strong>'Submit'</strong>.</li>
  <li>If the e-Book module is not activated (within the active tools), <strong>activate</strong> it through the 'Activation' link. <br />

  </li>
</ol>
<hr>
<h2>&nbsp;&nbsp;Structure of E-Book (Step 3)</h2>

<p>The previous step (Step 2) displays a screen with options and a table with the html page-files you uploaded within the zip file. You can define the structure of the electronic book, as follows:</p>

<ol>
  <li>First define <strong>sections</strong> for the E-Book. You can add as many as you need. Define an <strong>incremental number</strong> and a <strong>name</strong> for each section in fields <strong>ID</strong> and <strong>Title</strong> correspondingly.</li>

  <li>After that you have to define the E-Book <strong>subsections</strong>. To do that you have to:
    <ul>
      <li><strong>map</strong> each uploaded page-file with the previously defined sections and</li>
      <li>define for each subsection the incremental number of the subsection within the parent section. <br />

      <em><u>Note</u>: the platform automatically suggests as title for each subsection the title of the corresponding files (these names can be modified at a later stage).</em></li>
    </ul>  
</li>
  <li>After the pagefile and section/subsection pairing is complete click on <strong>'Submit'</strong>. </li>
  <li>The system informs you on the successful update and the e-Book is now available in the specified structure.</li>
</ol>

<hr>
<h2>&nbsp;&nbsp;E-Book browsing</h2>

<p>Select the E-Book module from the left menu and within the presented list, click on the name of the E-Book you wish to open.</p> 

<hr>

<h2>&nbsp;&nbsp;Modify - Delete E-Book</h2>
<p>If you want to modify an E-Book click on the <strong>'Modification'</strong>icon. <br />
Click on icon <strong>Delete</strong> to <strong>delete</strong> an E-Book.</p>
<hr>
<h2>&nbsp;&nbsp;Administration of html files</h2>
<p>If you want to administrate the uploaded html files in an E-Book,</p>
<ul>
  <li>click on icon <strong>Modification</strong>,</li>
  <li>select the <strong>File administration</strong> option. A page with all uploaded html files for this E-Book will be displayed,</li>
  <li>you can manage files and directories in this module as in module 'Documents'.</li>
</ul>
<hr>
<h2>&nbsp;&nbsp;Linking E-Book with course units</h2>
<p>You can link a specific course unit with the corresponding <strong>section</strong> of the <strong>E-Book</strong>. 
For accomplishing this, click on the course unit you want to link and from the available tools select <strong>'Add e-book'</strong>.
From the displaying form select the desired E-Book section.
After that click on <strong>'Add selection'</strong>.</p>";
$langEBook_studentContent = "<p>'eBook' is a set of <b>hypertext</b> content that 'simulates' a book (print version). Basically an electronic book is a flexible structure that apart from text in digital format can additionally be enriched with multimedia content such as images, videos, external links, etc. Moreover, this module allows structuring of the book contents into sections - subsections. The presentation of content is made through a list box. Navigation through an e-book is done in a friendly way as there are various tools for handling actions e.g. previous-next.</p>
<p>Select from the left menu the 'E-Book' option. A list of the course e-books will be displayed. To browse an e-book just click on it.</p>";
$langFor_studentContent = "<p>The platform allows you to communicate with all other course students.</p>
You can either create a new discussion topic by clicking 'New topic' (after you have first selected the corresponding discussion area), or reply to an existing topic by clicking 'Reply'.
Υou can also click on (<img src='$themeimg/email.png' width=16 height=16>) in order to start or stop receiving email notifications for new posts in a specific topic or forum category.</li>"
;
$langHMyAgenda = "My Agenda";
$langMyAgendaContent = "$langAgenda_studentContent";
$langHPersonalStats = 'Personal Statistics';
$langPersonalStatsContent = "<p>The number and duration of visits per course is presented here.</p>";
$langInfocoursContent = "<p>Sie können einige der Detailinformationen des Kurses ändern, wie den 
Namen des Dozenten, den Titel und den Zugriff auf den Kurs. Nach Vollendung der Änderungen klicken 
Sie auf 'Änderungen abschicken'.
</p>
<p><u>Zugang zum Kurs:</u></p>
<p><b>Offener Kurs :</b>jeder kann Zugang zum Kurs haben auch ohne Benutzerkonto.</p>
<p><b>Registrierung erforderlich :</b>nur Benutzer mit Benutzerkonto auf der Plattform haben Zugang.</p>
<p><b>Geschlossener Kurs :</b>nur die Benutzer haben Zugang zum Kurs, die in der Liste der Benutzer des Kurses sind</p>
<p><u>Andere Aktionen :</u></p>
<p><b>Sicherheitskopie des Kurses:</b>Sie können eine Sicherheitskopie des Kurses erzeugen und 
diese lokal auf Ihrem Rechner speichern. Für den Fall, dass Sie den Inhalt wiederherstellen möchten, 
müssen Sie sich an den Administrator der Plattform wenden.</p>
<p><b>Löschen des Kurses :</b> Beim Löschen des Kurses wird dessen Inhalt und die Benutzer des 
Kurses gelöscht (aber die Benutzer werden nicht von der Plattform gelöscht).</p>
<p><b>Erneuerung des Kurses :</b>Sie können selektiv einige der Daten des Kurses löschen, um den 
Kurs für ein neues akademisches Jahr vorzubereiten.</p>";
$langHGroupSpace = "User Groups";
$langGroupSpaceContent = "<p>To correct the user group info click on 'Edit this group'.
        Clicking on 'Forum' you enter the 'Forum' area where a distinct forum has been created for each user group. Click on 'Documents of the Group' in order to add or remove documents related to the group. Please note that these documents are only related to the specific group and have no relation with the 'Documents' module of the platform main screen.
        You may send an email to all users within a group by clicking on 'Email to group' selection.
         You may also view the group usage statistics by clicking on 'Usage Statistics'.</p>";
$langHAddCourseUnits = 'Add course unit content';
$langAddCourseUnitsContent = "<p class='helptopic'>Here you can add content or resources to the selected course unit. The types of resources that can be added are listed next to the 'Add:' label. By clicking on 'Add', you can find resources of the selected type available in your course. Tick the ones you would like to add and click on 'Add selected'. Newly added resources are immediately listed, with edit and delete icons for each one. Please note that when multiple course units exist in a course, links to the next and previous unit appear automatically. You can also use the selection box below to navigate directly to a specific unit.</p>";
$langHBBB = "Teleconference";
$langHBBB_student = $langHBBB;
$langBBBContent = "<p>To schedule a new teleconference for your course, please click on the \"Schedule New Teleconference\" button. 
You can use the \"Title\" field for a short descriptive title and the \"Description\" field for the agenda of the discussion or presentation.
        <br /><br />The other fields can be set as follows:</p> 
        <p>
        <ul>
        <li><strong>Teleconference start</strong>: Set the scheduled start date and time</li>
        <li><strong>Teleconference type</strong>: Public to others: All registered platform users will be able to join the teleconference, regardless of their being registered to this course or not -  Private: The teleconference will be accessible only to participants registered to your course.</li>
        <li><strong>Status</strong>: Visible: the teleconference will be displayed to users of your course - Invisible: the teleconference will be displayed only to the course administrators. Use the latter option to schedule the teleconference in advance but only display it at a later time.</li>
        <li><strong>Session availability</strong>: Select how many minutes before the scheduled start time participants will be able to join the teleconference.</li>
        <li><strong>Notify external participants</strong>: Enter a list of email recipients who will be invited to participate in the teleconference (e.g. visitors, external institution members with no access to the platform, etc).</li>
        <li><strong>Notify users for teleconference schedule</strong>: Enable this option to send an email notification to all participants.</li>
        </ul>
        </p>";
$langBBB_studentContent = "<p>Here you can find all scheduled course teleconferences. 
        <ul>        
        <li>To join one of them, please click on its title.</li>
        <li>The link becomes active in a predetermined period before the scheduled start time. This period is set by the course administrator.</li>
        </ul>
        </p>";
