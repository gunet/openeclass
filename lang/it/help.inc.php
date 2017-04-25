<?php

// Message file for language it
// Generated 2015-02-12 11:04:34

$langCourseAccessHelp = "<ul> 
<li><b><img src='$themeimg/lock_open.png' width=16 height=16>Corso aperto</b>. Questo modo di accesso consente l' Accesso Libero (senza registrazione) alla Home Page del corso, e senza richiedere una password.</li> 
<li><b><img src='$themeimg/lock_registration.png' width=16 height=16>Occorre registrazione</b>. Questo modo di accesso, consente l' accesso libero (con registrazione) per coloro che hanno già un conto sulla piattaforma. Qui si può digitare una password, ma è facoltativo.</li> 
<li><b><img src='$themeimg/lock_closed.png' width=16 height=16>Corso chiuso</b>. Questo modo di accesso consente l'accesso al corso (autorizzazione di scrivere) per coloro che sono nella lista degli utenti del corso.</li> 
<li><b><img src='$themeimg/lock_inactive.png' width=16 height=16>Corso inattivo</b>. Questo modo di accesso consente l'accesso al corso <b>solo</b> agli insegnanti del corso.</li> 
</ul>
";
$langWikiSyntaxHelp = "<h4>Sintassi dei comandi</h4>
<p>Creare pagine wiki e link fra di loro</p>
<p><strong>Parole  di wiki</strong>: Le parole di Wiki sono parole che sono scritte come <em>Parola</em>. Per creare una pagina wiki o un link a una pagina wiki, modifica una già esistente e compila il titolo della pagina secondo la sintassi, ad esempio <em>La mia pagina</em>, e quindi salvarla. Automaticamente la parola <em>La mia pagina</em> sarà sostituita con un link alla pagina Wiki <em>La mia pagina </em>&nbsp;</p>
<p><strong><p>Link Wiki </strong>: I link Wiki sono come i link ipertestuali (hyperlink - guarda più avanti), tuttavia non si riferiscono ad un protocollo (ad esempio <em>http://</em> o <em>ftp://</em>) e si trasformano automaticamente in link alle pagine Wiki. Per creare una nuova pagina o un link ad una già esistente, modifica la pagina ed aggiungi <code>[titolo della pagina]</code> o <code>[nome di link | titolo della pagina]</code> al contenuto. Puoi utilizzare la seguente sintassi per rinominare un link Wiki: <code>[Nome del link | parola]</code>.</p>
<ul>Link ipertestuali
<li><code>[url]</code>, <code>[nome | url]</code>, <code>[nome | url | lingua]</code> o <code>[nome | url | lingua |. titolo]</code>.&nbsp;</li></ul>
<ul> Inserire Immagine
<li><code>((url | alt text ))</code>, <code>((url | alt text | posizione))</code> o <code>((url | alt text | posizione | descrizione ))</code>. <br /> Il parametro di posizione può assumere i seguenti valori: L (sinistra), R (destra) o C (centro).&nbsp;
Inoltre è possibile utilizzare la sintassi dei link ipertestuali. Per esempio <code>[titolo | image.gif]</code>. Questa sintassi sta per essere abolita, qundi utilizza quella precedente per sicurezza.</li></ul>
<ul>Link a un' immagine
<li>Si tratta di un link ipertestuale, però metti un 1 come quinto parametro per evitare il caricamento dell' immagine, sicché risulti un link a un' immagine. Per esempio <code>[image | image.gif || 0]</code> presenterà un link all' immagine image.gif, anziché l'immagine stessa </li> </ul>
<p>Impaginazione</p>
<ul>
<li><strong>Corsivo</strong>: racchiudi il testo tra virgolette semplici <code>''testo''</code>&nbsp;</li>
<li><strong>Grassetto</strong>: racchiudi il testo tra tre virgolette singole <code>'''testo'''</code>&nbsp;</li>
<li><strong>Sottolineato</strong>: racchiudi il testo tra due trattini bassi (underscore) <code>__testo__</code>&nbsp;</li>
<li><strong>Barrato</strong>: racchiudi il testo tra due trattini <code>--testo--</code>&nbsp;</li>
<li><strong>Titolo</strong>:!!!<code></code>, <code>!!</code>, <code>!</code> per i titoli, sottotitoli, sottotitoli-minori rispettivamente&nbsp;</li>
<li>Lista</li>
La linea deve iniziare con <code>*</code> (lista non ordinata) o <code>#</code> (lista ordinata). Puoi disordinare le liste (<code>*#*</code>) per creare delle liste in vari livelli&nbsp;.
<li>Paragrafo</li>
Separa i paragrafi separati con una o più linee
</ul>

<h4>Sintassi avanzata</​​h4>
<ul>
<li>Nota a piè</​​li>
<code>Testo della nota</​​code>&nbsp;
<li>Testo in formato pronto</li>
Inizia ogni riga con uno spazio &nbsp;
<li>Blocco  di riferimento</li>
<code>&gt;</code> o <code>;:</code> prima di ogni riga&nbsp;
<li>Linea orizzontale</li>
<code>----</code>
<li>Interruzione di riga obbligatoria</li>
<code>%%%</code>&nbsp;
<li>Acronimo</li>
<code>??acronimo??</code> o <code>??acronimo|definizione??</code>&nbsp;
<li>Riferimento diretto</li>
<code>{{riferimento}}</code>, <code>{{riferimento|lingua}}</code> o <code>{{riferimento|lingua|url}}</code>&nbsp;
</ul>
</ul>
<p>Il link 'Attivare' / 'Disattivare' muove il sistema Wiki dagli strumenti attivi a quelli inattivi e vice versa.</p>";
$langWindowClose = 'Chiudi finestra';
$langHDefault = 'Guida non disponibile';
$langDefaultContent = '<p>Non è disponibile alcun testo di aiuto per la pagina della piattaforma che stai visualizzando ora.</p>';
$langPHPMathPublisher = "<p><b>Supporto di simboli matematici</b></p> 

<p>Per inserire dei simboli matematici nei tuoi documenti, puoi utilizzare i
<a href='../../manuals/PhpMathPublisherHelp.pdf' target='_blanκ'>comandi</a>
  (simboli) che fornisce il PhpMathPublisher. 
  Più dettagliatamente, utilizza l' editor di testo per inserire il tuo contenuto.
  Se nel contenuto che inserisci ci sono dei simboli matematici che desideri visualizzare, quindi:</p> 
<ol> 
<li>Clicca sull' icona 'Visualizza il codice HTML'.</li> 
<li>Inserisci i simboli matematici tra i tag [m]...... [/m]</li>
</ol>
<p>Ad esempio per inserire la radice quadrata di 'a' digita [m]sqrt{a}[/m]</p>";
$langHFor = "Forum";
$langHFor_student = $langHFor;
$langForContent = "<p>Puoi comunicare con tutti gli utenti del corso.</p>
<p>Per creare un nuovo Forum, devi prima associarlo a una categoria. Cliccando sul link 'Aggiungi Categoria' puoi inserire una nuova categoria. Per associare un Forum a una certa categoria devi cliccare sul pulsante (<img src='$themeimg/add.png' width=16 height=16>) che si trova a destra della categoria selezionata. Compila il titolo e una descrizione del forum e premi dunque sul pulsante 'Aggiungere'.</p>
<hr>
<p><b>Operazioni:</b><br><br>
<ul>
  <li>Aggiungere un nuovo forum. Cliccando sul tasto (<img src='$themeimg/add.png' width=16 height=16>) puoi inserire un nuovo forum.</li>
  <li>Modificare un forum. Cliccando sul tasto (<img src='$themeimg/edit.png' width=16 height=16>) puoi modificare un forum esistente.</li>
  <li>Eliminare un forum. Cliccando sul tasto (<img src='$themeimg/delete.png' width=16 height=16>) puoi eliminare un forum esistente.</li>
  <li>Notifica tramite una e-mail con l' invio di risposte. Cliccando sul tasto (<img src='$themeimg/email.png' width=16 height=16>) puoi attivare la notifica tramite e-mail quando un post o una risposta al forum della propria categoria viene apposta.</li> 
  </ul>
";
$langHDoc = 'Documenti';
$langHDoc_student = $langHDoc;
$langDocContent = "<p>Il modulo 'Documenti' è uno strumento importante che aiuta notevolmente lo
studente alla comprensione della materia del corso, siccome gli fornisce del
materiale didattico.  Puoi caricare file di qualsiasi tipo (HTML, Word,
Powerpoint, Excel, Acrobat, Flash, Quicktime, ecc.). L'unica limitazione è l'
applicazione adatta deve essere installata per poter legere i documenti.</p>
<p>Certe operazioni fondamentali vengono fornite dal modulo 'Documenti'. </p>

<h4>Caricare un file</h4>
<ul>
  <li>Clicca  sul link 'Caricare file al server.' Poi, al modulo che viene
      visualizzato, utilizza il pulsante 'Sfoglia' per selezionare il file sul tuo
      computer</li>
  <li>Esegui il caricamento cliccando sul pulsante 'Caricare'.</li>
</ul>
<h4>Eliminare un file (o cartella)</h4>
<ul>
<li>Clicca l'icona 'Cancellare' (<img src='$themeimg/delete.png' width=10 height=10>) </li>
</ul>
<h4>Scaricare una cartella intera</h4>
<ul>
  <li>Se desideri salvare sul computer la cartella intera dei documenti,
      puoi premere il pulsante (<img src='$themeimg/save_s.png' width=16 height=16>)
      che si trova proprio sulla destra della cartella root. Per salvare solo una
      cartella specifica, clicca su un pulsante simile, che questa volta si trova a
      destra della cartella che desideri.</li>
</ul>

<h4>Spostare un file (o cartella)</h4>
<ul>
  <li>Clicca sull'icona 'Spostare' (<img src='$themeimg/move.png' width=10 height=10>)</li>
  <li>Seleziona la cartella in cui desideri spostare il documento (o la
      cartella) (nota: la definizione 'root directory' (cartella root) significa che
      non puoi andare sopra di questo livello nella struttura alberata del file
      server).</li>
  <li>Convalida l' operazione facendo clic su 'Spostare'.</li>
</ul>

<h4> Rinominare un file (o cartella) </h4>
<ul>
  <li>Clicca sull' icona 'Rinominare' (<img src='$themeimg/rename.png' width=16 height=16
>)</li>
  <li> Digita il nuovo nome nella casella (in alto a sinistra ).</li>
  <li> Convalida facendo clic su 'Rinominare' </li>
</ul>
<h4>Aggiungere o modificare un commento ad un file (o una cartella)</h4>
<ul>
  <li>Fare clic sull'icona  'Commento' (<img src='$themeimg/comment_edit.png' width=16  height=16>)
      alla colonna</li>
  <li>Inserisci un nuovo commento nella casella corrispondente (in alto a destra).</li>
</ul>
<h4>Rendere un file (o cartella) invisibile dagli studenti</h4>
<ul>
  <li>Clicca sull'icona 'Visibile/Invisibile' (<img src='$themeimg/visible.png' width=10 height=10>)</li>
  <li>Dopo questa operazione, il file (o la cartella) esiste ma non sarà visibile dagli studenti.</li>
  <li>Per recuperare la sua visibilità, clicca su 'Visibile/Invisibile'
      (<img src='$themeimg/invisible.png' width=14 height=10>)</li>
</ul>
<hr>

<h4>Creare una cartella</h4>
<p>Puoi organizzare il tuo contenuto in cartelle. Per creare una cartella: </p>
<ul>
  <li>Clicca  sul link 'Creare una cartella'</li>
  <li>Inserisci il nome della nuova cartella nella casella corrispondente.</li>
  <li>In seguito clicca su 'Creare cartella'.</li>
</ul>
<hr>

<h4><b>Visualizzare lo spazio sul server disponibile</b></h4>
<p>Puoi vedere il tuo spazio sul server disponibile</p>
<ul>
  <li>Fai clic sul link 'Visualizza Spazio Server'</li>
  <li>Sul panello che appare, vengono visualizzati lo spazio utilizzato, il tasso di utilizzo, e lo spazio totale disponibile.</li>
</ul>
<p>Il link 'Attivare/Disattivare' raggiunge il trasferimento dei documenti dagli strumenti attivi a quelli inattivi e vice versa.</p>
";
$langDoc_studentContent = "<p>Puoi scaricare dei file o delle cartelle cliccando sul proprio titolo.
<h4>Scaricare la cartella intera</h4> 
<p>Se desideri salvare nel tuo computer l'elenco interο dei documenti
del corso, clicca sull' icona (<img src='$themeimg/save_s.png'
width=16 height=16>).</p>";
$langHUser = "Gestione Utenti";
$langUserContent = "<p>Questo modulo permette la <b>gestione</b> di tutti gli utenti registrati
alla piattaforma. Inoltre fornisce una serie di informazioni su: il numero
totale degli utenti registrati alla piattaforma, il numero degli insegnanti, il
numero degli studenti e il numero degli ospiti.</p>
<p>Cliccando su 'Aggiungere' hai alla tua disposizione una serie di opzioni
come:</p>
<ul>
  <li><b>Aggiungere un utente.</b> Puoi aggiungere un utente compilando
  il suo nome e cognome o il username.</li>
  <li><b>Aggiungere più utenti.</b> Con questa opzione è possibile aggiungere 'di
        massa' degli utenti. </li>
  <li><b>Aggiungere degli utenti ospiti.</b>
      Se desideri aggiungere un utente ospite, compila semplicemente la
      password che avrà per accedere alla piattaforma.</li>
  <li><b>Cerca Utenti.</b> È possibile cercare un utente registrato compilando
      il suo nome e cognome o il suo username.</li>
  <li><b>Gestione di Gruppi di Utenti.</b> Una serie di operazioni (creare,
      eliminare, aggiungere tutti, eliminare tutti) che riguardano ai gruppi
      di utenti sono disponibili selezionando l' opzione specifica.</li>
</ul>
<hr>
<p><b>Gestione di ruoli</b></p>
<p>Un numero di <b>autorizzazioni</b> sono forniti dalla piattaforma sulla
gestione degli utenti, come segue: </p>
<ul>
  <li><img src='$themeimg/teacher_add.png' width=24 height=16>Aggiungere
      l' autorizzazione dell' insegnante. Come insegnante del corso,
      puoi specificare le autorizzazioni degli altri utenti e dei
      vari parametri del corso. </li>
  <li><img src='$themeimg/assistant_add.png' width=24 height=16>Aggiungere
      l' autorizzazione dell' insegnante di sostegno. La sua
      responsabilità principale è quella di attivare / disattivare
      degli strumenti e 'caricare' tutti i documenti necessari del
      corso. La concessione di una tale autorizzazione funziona come
      supporto al insegnante del corso.</li>
  <li><img src='$themeimg/group_manager.png' width=16 height=16>
      Aggiungere l' autorizzazione del manager del gruppo. Come
      manager del gruppo puoi specificare gli utenti che compongono
      il gruppo e una serie di parametri ad esso associati. </li>
</ul>
<hr>
<p>Questo modulo offre la possibilità di
'Cancellarsi' da un corso. Per il processo della
'Cancellazione' dal corso premi il pulsante lezione
<img src='$themeimg/cunregister.png' width=16 height=16>.</p>
";
$langHGuest = "Utente Ospite";
$langGuestContent = "<p>Selezionando 'Aggiungere utente ospite' puoi aggiungere un nuovo utente ospite.</p>";
$langHQuestionnaire = "Questionario";
$langHQuestionnaire_student = $langHQuestionnaire ;
$langQuestionnaireContent = "<p>Lo strumento 'Questionario' ti permette di <b>creare</b> sia delle domande
a 'Scelta multipla' che domande a 'Compilazione'. Per creare un questionario,
clicca sul link 'Creare Questionario'. Nel display che appare, compila il
titolo e il periodo per cui rimarrà attivo il tuo questionario. Poi seleziona
il tipo di domanda desiderato cliccando su uno dei link 'Nuova domanda a scelta
multipla' o 'Nuova domanda a compilazione'. Il processo viene completato
facendo clic su 'Creare Questionario'. </p> 
<p><img src='$themeimg/warning.png' width=18 height=18> <b> Avviso</b>: Se in
un questionario attivo, qualche studente ha risposto almeno una domanda, il
processo della correzione del questionario si realizza con la creazione di un
'nuovo' (del corretto). Questo processo si effettua automaticamente dalla
piattaforma e garantisce la massima affidabilità dei risultati dei questionari.
</p>

<hr> 
<p><b>Operazioni supportate dal modulo 'Questionario'</b></p>
<p>Le azioni che sono supportati dalla piattaforma sul modulo 'Questionario'
sono:</p>
<ul> 
  <li>Modificare questionario. Per modificare un questionario, clicca su
      <img src='$themeimg/edit.png' width=16 height=16> che si trova a destra.</li>
  <li>Eliminare questionario. Per eliminare un questionario, basta fare clic sul
      pulsante (<img src='$themeimg/delete. png' width=16 height=16 >) che si trova
      a destra.</li> 
  <li>Visibile/Invisibile. Per cambiare la visibilità del tuo questionario clicca
      su <img src='$themeimg/visibile.png' width=16 height=16> che si trova a destra.</li>
</ul>
";
$langQuestionnaire_studentContent = "<p>Lo strumento dei questionari permette l' <b>invio</b> di risposte sia a
domande a 'Scelta multipla' che a domande a 'Compilazione'. Per rispondere a un
questionario clicca sul suo titolo. Al display che appare, vengono presenti i
dati che riguardano il medesimo questionario come: il suo titolo, la data della
sua creazione, il suo inizio e la sua fine e se ci hai partecipato o no.
Rispondi a tutte le domande nel questionario e clicca su 'Invio' da concludere
la procedura.</p> 
<p><img src='$themeimg/warning. png' width=18 height=18>Se in un questionario
attivo, qualche studente ha risposto almeno una domanda, il processo della
correzione del questionario si realizza con la creazione di un 'nuovo' (del
corretto). Questo processo si effettua automaticamente dalla piattaforma e
garantisce la massima affidabilità dei risultati dei questionari.
</p>";
$langHExercise = "Esercizi";
$langHExercise_student = $langHExercise ;
$langExerciseContent = "<p>Il modulo 'Esercizi'  aiuta alla autovalutazione degli studenti in una serie di esercizi che sono stati creati dall' insegnante del corso. Nella home page degli esercizi vengono visualizzati tutti gli esercizi del corso e una serie di operazioni è fornita per la loro gestione (Modificare, Eliminare , Cambiare Visibilità). <br>
<br>I tipi di domande che puoi includere in un esercizio sono i seguenti: <br>
<ul>
  <li><a href='#syn7'>Scelta multipla (risposta singola)</a></li>
  <li><a href='#syn8'>Scelta multipla (risposte multiple)</a></li>
  <li> <a href='#syn9'>Corrispondenza </a></li>
  <li> <a href='#syn10'>Compilare i campi vuoti</a> </li>
  <li> <a href='#syn11'>Vero / Falso</a></li>
</ul>
<p>Per creare un esercizio, seleziona prima il link 'Nuovo Esercizio '. Quindi digita il titolo e la descrizione dell' esercizio e imposta una serie di parametri associati a questo esercizio. Tra gli altri è possibile specificare la modalità di visualizzazione dell'esercizio (in una pagina o una pagina per ogni domanda) e il limite di tempo al quale si applica (inizio - fine). Scegli anche il numero dei tentativi dell' esercizio disponibili allo studente, ed ancora imposta se dopo la conclusione dell'esercizio verranno visualizzate le risposte esatte e il punteggio. Il processo si completa cliccando sul link 'Creare'. </p>
<hr>
<p><b><a name='syn7'>Scelta multipla (risposta singola)</a></b><br><br>
Con l' uso della domanda a 'scelta multipla (risposta singola)' lo studente sviluppa la capacità di logica combinatoria alla soluzione dei problemi, siccome egli mette in ordine e categorizza gli oggetti. Per creare una domanda 'a scelta multipla (risposta singola)' basta cliccare su 'Nuova domanda'. In seguito digita un titolo e un commento sull'esercizio. La piattaforma ti offre la possibilità di inserire anche immagini all'interno degli esercizi con l' impostazione  'Aggiungere immagine' e il pulsante 'Sfoglia'. Seleziona il tipo di esercizio e fai clic su 'Ok'. Inserisci dunque le domande e volontariamente il voto (peso) di ciascuno. Seleziona la risposta esatta e aggiungi o rimuovi le domande tramite i pulsanti +ri, -ri. Il processo sarà compiuto se fai clic su 'Creare'. </p>
<hr>
<p><b><a name='syn8'>Scelta multipla (risposte multiple)</a></b><br><br>
Con l' uso delle domande a 'Scelta multipla (risposte multiple)'  l'insegnante fornisce agli studenti tutte i mezzi che sono necessari per lavorare in 'proprio'. Per creare una domanda a 'Scelta multipla (risposte multiple)' basta cliccare su 'Nuova domanda'. In seguito digita un titolo e un commento sull'esercizio. La piattaforma ti offre la possibilità di inserire anche immagini all'interno degli esercizi con l' impostazione  'Aggiungere immagine' e il pulsante 'Sfoglia'. Seleziona il tipo di esercizio e fai clic su 'Ok'. Inserisci dunque le domande e volontariamente il voto (peso) di ciascuno. Seleziona la risposta esatta e aggiungi o rimuovi le domande tramite i pulsanti +ri, -ri. Il processo sarà compiuto se fai clic su 'Creare'. </p>
<hr>
<p> <a name='syn9'>Corrispondenza </a> </b> <br>
Negli esercizi di corrispondenza, lo studente può combinare dati provenienti da due serie (colonna A e colonna B), da cui risulta la risposta corretta. Per creare una domanda a 'Corrispondenza' basta cliccare su 'Nuova domanda'. In seguito digita un titolo e un commento sull'esercizio. La piattaforma ti offre la possibilità di inserire anche immagini all'interno degli esercizi con l' impostazione  'Aggiungere immagine' e il pulsante 'Sfoglia'. Seleziona il tipo di esercizio 'Corrispondenza' e fai clic su 'Ok'. Poi inserisci i dati delle colonne A e B, e quindi, scegliendo i valori dalle proprie liste, imposta la loro corrispondenza. Aggiungi o rimuovi le domande tramite i pulsanti +ri, -ri. Il processo sarà compiuto se fai clic su 'Creare'. </p>
<hr>
<p> <a name='syn10'>Compilare i campi vuoti</a></b> <br><br>
Le domande di questo tipo hanno come obbiettivo di rendere gli studenti trovare le parole mancanti in un testo. Per creare una domanda a 'Compilare i campi vuoti' basta cliccare su 'Nuova domanda'. In seguito digita un titolo e un commento sull'esercizio. La piattaforma ti offre la possibilità di inserire anche immagini all'interno degli esercizi con l' impostazione  'Aggiungere immagine' e il pulsante 'Sfoglia'. Seleziona il tipo di esercizio 'Campi vuoti' e fai clic su 'Ok'. Inserisci dunque il testo che desideri e le parole che vanno compilate dagli studenti, racchiuse in []. Il processo viene compiuto cliccando su 'Convalidare'. </p>
<hr>
<p><a name='syn11'> Vero / Falso </a> </b> <br><br>
In questo tipo di domande gli studenti scelgono tra due risposte a una certa domanda. Solo una risposta è giusta e l'altro sbagliata. Usando le domande 'Vero / Falso' l' insegnante può esaminare,  con un processo breve,  la conoscenza dello studente su un certo tema. Per creare una domanda 'Vero / Falso' basta fare clic sul 'Nuova domanda'. In seguito digita la domanda e un eventuale commento e clicca su 'Ok'. Nel display successivo inserisci le due risposte e segnala quella giusta. Il processo viene compiuto facendo clic su 'Creare'. </p>
<hr>
<p><b>Operazioni agli esercizi supportate</b><br> <br>
Le operazioni che sono supportate dalla piattaforma riguardante al modulo degli esercizi sono le seguenti:
<ul>
  <li>Modificare un esercizio. Per modificare un esercizio, basta fare clic sul pulsante (<img src='$themeimg/edit.png' width=16 height=16> che sta a destra.</li>
  <li>Eliminare un esercizio. Per eliminare un esercizio, basta fare clic sul pulsante (<img src='$themeimg/delete.png' width=16 height=16>) che sta a destra.</li>
  <li>Visibile / Invisibile. Per rendere visibile / invisibile un esercizio, fai clic sul pulsante (<img src='$themeimg/visible.png' width=16 height=16>) che sta a destra.</li>
  <li>Riuso di una domanda da un altro esercizio. Puoi riusare una domanda da un altro esercizio, prima cliccando su 'Modificare' e poi sul link 'Domanda da un altro esercizio'</li>
  <li>Visualizzare gli esercizi in ordine casuale. Durante la creazione di un esercizio, segnala l' opzione opzione 'ordine casuale' e specifica il numero delle domande che verranno visualizzate nell'esercizio. Questa funzione è raccomandata agli esami del corso</li>
  </ul>
 <hr>
 <p><b>Valutazione degli Esercizi</b> <br><br>
 Sulla destra di ogni domanda che viene creata, c' è un 'peso' che corrisponde alla sua valutazione. I valori supportati dalla piattaforma per la valutazione delle domande sono: valutazione positiva, valutazione negativa e valutazione zero. Il voto negativo ha un senso solo alle categorie 'Scelta multipla (risposta singola)' e 'Scelta multipla (risposte multiple) '. La somma delle risposte corrette potenziali  (cioè di tutti i pesi positivi), ci dà il 'perfetto'.

 </p>

";
$langExercise_studentContent = "<p>Il modulo 'Esercizi'  aiuta alla tua autovalutazione in una serie di esercizi che sono stati creati dall' insegnante del corso. Nella home page degli esercizi vengono visualizzati tutti gli esercizi del corso, e occorre essere particolarmente attenti a:   le date di inizio e fine dell' esercizio, il suo limite di tempo e il numero di tentativi che lo puoi eseguire. Per eseguire un esercizio <b>clicca</b> sul suo nome.<br>
<br>I tipi di domande che puoi rispondere in un esercizio sono i seguenti: <br>
<ul>
  <li><a href='#syn7'>Scelta multipla (risposta singola)</a></li>
  <li><a href='#syn8'>Scelta multipla (risposte multiple)</a></li>
  <li> <a href='#syn9'>Corrispondenza </a></li>
  <li> <a href='#syn10'>Compilare i campi vuoti</a> </li>
  <li> <a href='#syn11'>Vero / Falso</a></li>
</ul>
<hr>
<p><b><a name='syn7'>Scelta multipla (risposta singola)</a></b><br><br>
Con l' uso della domanda a 'scelta multipla (risposta singola)' hai la possibilità di sviluppare la capacità di logica combinatoria alla soluzione dei problemi, siccome metti in ordine e categorizzi gli oggetti. Scegli un esercizio e rispondi alle proprie domande. Ogni domanda contiene corrisponde a <b>una</b> sola risposta. Per passare da una schermata ad un'altra domanda cliccare su 'Continuare' (o 'Annullare', rispettivamente). Il processo è completato, fare clic su 'Fine'.
<hr>

<p><b><a name='syn8'>Scelta multipla (risposte multiple) </a> </b> <br><br>
Utilizzando le domande a 'Scelta multipla (risposte multiple)' l' insegnante ti dà tutti quei mezzi che sono necessari per lavorare in 'proprio'. Scegli un esercizio e rispondi alle proprie domande. Ogni domanda corrisponde a <b>più di una</b> risposte. Per passare da una domanda all' altra clicca su 'Continuare' (o 'Annullare', rispettivamente). Il processo finisce, facendo clic su 'Fine'.</p>

<hr>
<p><b><a name='syn9'>Corrispondenza </a></b> <br> <br>
Agli esercizi di corrispondenza, bisogna combinare i dati provenienti da due serie (colonna A e colonna B), da cui risulta la risposta corretta. Per passare da un esercizio all' altro clicca su 'Continuare' (o 'Annullare', rispettivamente). Il processo viene compiuto, facendo clic su 'Fine'.
</ P>

<hr>
<p> <a name='syn10'>Compilare i campi vuoti</a></b> <br><br>
Domande di questo tipo sono stati progettati, affinché  tu trovi le parole mancanti nei campi vuoti [] di un testo. Scegli un esercizio e compila i campi vuoti che contiene. Ogni domanda contiene corrisponde a più di una risposte. Per passare da una domanda all' altra, clicca su 'Continuare' (o 'Annullare', rispettivamente). Il processo finisce, facendo clic su 'Fine'. </p>
<hr>

<p><b><a name='syn11'>Vero / Falso</a></b> <br><br>
In questo tipo di domande devi scegliere tra due risposte a una certa domanda. Solo una risposta è esatta e l'altra è sbagliata. Scegli un esercizio e ad ogni domanda rispondi 'Vero' o 'Falso'. Per passare da una domanda all' altra clicca su 'Continuare' (o 'Annullare', rispettivamente). Il processo si conclude, cliccando su 'Fine'. </p>

<hr>
<p><b>Valutazione degli Esercizi</b><br><br>
Sulla destra di ogni domanda c'è un 'peso' che corrisponde alla sua valutazione. I valori supportati dalla piattaforma per la valutazione delle domande sono: valutazione positiva, valutazione negativa e valutazione zero. Il voto negativo ha un senso solo alle categorie 'Scelta multipla (risposta singola)' e 'Scelta multipla (risposte multiple) '. La somma delle risposte corrette potenziali  (cioè di tutti i pesi positivi), ci dà il 'perfetto'.
</p>
";
$langHWork = "Compiti";
$langHWork_student = $langHWork ;
$langWorkContent = "<p>Il modulo 'Compiti'  è un sistema integrato per creare e gestire dei compiti.</p> 
<p>Come un insegnante, puoi creare un compito facendo clic sul link <b>Creare compito'</b>.
Compila il titolo del compito e specifica la data del suo invio. Facoltativamente puoi aggiungere un commento. Questo modulo contiene due tipi di compiti: 'Individuale', 'Collettivo'. Se selezioni 'Collettivo' come tipo di compito,  devi avere attivato il modulo  'Gruppi di utenti' in anticipo.</p> 
<p>Dopo la creazione del compito, non dimenticare di attivarlo premendo l'icona <img src='$themeimg/invisible.png' border='0' align='absmiddle'>. Il compito sarà visibile e accessibile agli studenti, poiché sia attivato. Puoi in qualsiasi momento modificare il compito cliccando sull' icona <img src='$themeimg/edit.png' border='0' align='middle'> o eliminarlo cliccando sull'icona  (<img src='$themeimg/delete.png' border='0' align='middle'>).
Cliccando sul titolo del compito, visualizzi i compiti già inviati dagli studenti.
A questo punto sono visibile i dati dello studente come: il numero di matricolazione, la data dell' invio del compito e il file sorgente del compito. Cliccando su 'Scaricare tutte i compiti in formato .zip' puoi 'scaricare' tutti i file di un certo compito che sono stati inviati dagli studenti.
Per dare i voti a un compito compila il punteggio rispettivo accanto al nome dello studente e premi sul link <b>'Assegnare Punteggio'</b>. Lo studente è in grado di vedere il suo voto cliccando sul titolo del compito.</p> 
<p>Tutti i compiti del corso sono visualizzati all' elenco dei compiti. Questo elenco comprende oltre al titolo del compito: la data finale dell' invio, il punteggio dall' insegnante e un' indicazione se il compito viene inviato o non dallo studente.
Per inviare il compito, basta premere sul suo titolo. Nel caso in cui lo studente ha già inviato un compito e lo vuole modificare, dovrebbe inviare di nuovo il compito modificato. Così la piattaforma rimuove automaticamente il 'vecchio'  e lo sostituisce con il nuovo.</br></br>. 
<img src='$themeimg/warning.png' width=18 height=18>. Tieni presente che dopo la <b>data finale</b> dell' invio di un compito, lo studente non lo può più inviare.
</p> 
<hr> 
$langPHPMathPublisher";
$langWork_studentContent = "<p>Il modulo 'Compiti'  è uno strumento utile del corso elettronico che permette l' <b>invio</b> e la <b>valutazione</b> elettronica dei compiti del corso. In particolare, gli studenti hanno la possibilità di caricare sulla piattaforma i loro compiti fino alla data finale, e poi, poiché l' insegnante abbia dato il voto, essi lo possono vedere. Tutti i compiti del corso sono visibili nella cartella dei compiti. La suddetta cartella  contiene oltre al titolo del compito: la data finale dell' invio,  il punteggio dall' insegnante e un' indicazione di se lo studente ha inviato il compito o non. Per inviare il compito, basta  cliccare sul proprio titolo. Se lo studente ha già inviato un compito e lo vuole modificare, dovrebbe inviare di nuovo il compito corretto.  Così la piattaforma automaticamente elimina il 'vecchio' compito e lo sostituisce con il 'nuovo'.</p> <p><img src='$themeimg/warning.png' width=18 height=18>Nota che dopo la data finale dell' invio di un compito, un eventuale invio in posticipo non è possibile.</br>&nbsp;&nbsp;  Se un compito è di gruppo, devi iscriverti a un gruppo, prima di inviarlo.";
$langHGroup = "Gruppi di Utenti";
$langHGroup_student = $langHGroup;
$langGroupContent = "<p>Il modulo 'Gruppi di utenti'  favorisce la collaborazione e l'interazione degli studenti con la loro organizzazione in gruppi. In questo modo si rinforza l'apprendimento collaborativo, si risolvono varie domande, e si scambiano dei pareri su argomenti particolari del corso. Ordinando in categorie le operazioni che si possono eseguire nel modulo 'Gruppi di utenti', si possono citare le seguenti:</p>
<ul>
  <li> <a href='#syn1'>Operazioni fondamentali dei gruppi di utenti</a></li>
  <li> <a href='#syn2'>Impostazioni dei gruppi di utenti</a></li>
  <li> <a href='#syn3'>Impostazioni di massa di gruppi di utenti</a></li>
  <li> <a href='#syn4'>Modificare un gruppo di utenti</a></li>
</ul>
<hr noshade size=1>
<p><b><a name='syn1'>Operazioni fondamentali dei gruppi di utenti</a> </b> </p>
<p>Le funzioni fondamentali del gruppo di utenti sono:</p>
<p><b>a) Creare un gruppo di utenti.</b> Per creare un gruppo di utenti compila il numero del gruppo o dei gruppi di utenti da creare e il numero dei propri membri. Poi fai clic su 'Creare'. </p>
<p><b> b) Modificare i parametri di un gruppo di utenti.</b> Per modificare alcuni parametri di un gruppo di utenti basta fare clic su <img src='$themeimg/edit.png' width=16 height=16> che sta a destra. Puoi modificare i parametri del gruppo, come il suo nome, il manager del gruppo e il numero massimo dei partecipanti. Comunque, l' operazione più importante è lo spostamento dei partecipanti dentro e fuori dal gruppo, facendo clic sui pulsanti <img src='$themeimg/next.png' width=13 height=13> e <img src='$themeimg/back.png' width=13 height=13> rispettivamente. Il processo si completa premendo il pulsante 'Modificare'. </p>
<p><b> c) Eliminazione di un gruppo di utenti.</b> Per eliminare un gruppo di utenti, fai clic sul pulsante (<img src='$themeimg/delete.png' width=16 height=16>) che si trova sulla destra. </p>
<hr noshade size=1>
<p><b> <a name='syn2'>Impostazioni dei gruppi di utenti </a></b></p>
<p>Una serie di impostazioni dei gruppi di utenti ti permette di personalizzare il funzionamento del gruppo, sia alle vostre esigenze che alle necessità del corso. Tra gli altri, puoi stabilire se gli studenti potranno <b>iscriversi</b> da soli ai gruppi di utenti, oppure se occorre il consenso da parte dell' insegnante. Basta segnalare una delle due opzioni nella casella di spunta appropriata per abilitare l' impostazione corrispondente. Inoltre puoi scegliere se il gruppo avrà <b>forum</b> o non, segnalando le caselle di controllo corrispondenti. Se desideri che l'accesso al forum sia <b>chiuso</b> (la partecipazione al forum si permette solo ai membri del gruppo) o <b>aperto</b> (ogni studente sarà in grado di leggere e scrivere dei messaggi), basta segnalare una delle due opzioni del modulo disponibili. Infine, dipende da te se nel gruppo particolare ci sono i <b>documenti</b> o non, seleziona la casella appropriata. In questo punto va notato che questi documenti riguardano al gruppo di utenti attuale e <b>non</b> sono associati al sistema globale dei 'documenti' sulla Home Page della piattaforma. </p>
<hr noshade size=1>
<p> <a name='syn3'>Impostazioni di massa di gruppi di utenti</a></b></p>
<p>Il modulo  'Gruppi di utenti' offre una gamma di operazioni di massa che puoi effettuare nell' ambito del gruppo o dei gruppi che hai creato. In particolare: </p>
<ul>
<li><b>Eliminare</b> tutti i gruppi di utenti. Con una mossa sola puoi eliminare tutti i gruppi che hai creato. Questa operazione può essere molto utile alla fine dell' anno accademico, quando il diario annuale sta per finire.</li>
<li><b>Completatare</b> tutti i gruppi di utenti. Rendendo conto tutti gli utenti registrati al corso,  la piattaforma aiuta l' insegnante creare in modo casuale dei gruppi di utenti (studenti), con lo strumento 'Riempire tutti i gruppi'.</li>
<li><b>Svuotare</b> tutti i gruppi. Con questa operazione l' insegnante può rimuovere gli utenti da tutti i gruppi.</li>
</ul>
<hr noshade size=1>
<p><b><a name='syn4'>Modificare un gruppo di utenti</a></b></p>
<p>Per modificare un gruppo di utenti, questo modulo ti offre due operazioni fondamentali: In particolare: </p>
<p><b>a) Area per il gruppo di utenti.</b>Una serie di informazioni utili (nome del gruppo di utenti, manager del gruppo,  membri del gruppo) sul gruppo attuale sono alla tua disposizione attraverso questo modulo. Inoltre, questa operazione fornisce una serie di link: </p>
<ul>
<li>Modificare un gruppo di utenti. Si tratta proprio di un link alla  gestione dei dati del gruppo. </li>
<li>Forum. Puoi creare un nuovo argomento da discutere che riguarda il gruppo attuale. Le operazioni supportate sono le seguenti: 'Nuovo Argomento', 'Eliminare Argomento' e la 'Notificare via e-mail per, le eventuali risposte inviate'. In questo punto va sottolineato che il post di un argomento sarà visualizzato anche dal modulo 'Forum' della Home Page della piattaforma. </li>
<li>Documenti del Gruppo. Puoi aggiungere o rimuovere,  attraverso i link rispettivi, i documenti che si riferiscono al gruppo attuale. Anche in questo punto, va sottolineato che i documenti caricati riguardano il ​​gruppo attuale e non hanno rapporto con il modulo 'Documenti' della Home Page della piattaforma. È inoltre possibile creare delle cartelle in cui puoi sistemare i documenti del gruppo caricati,  facendo clic sul link 'Creare Cartella'. Una caratteristica molto utile che riguarda lo spazio al server che hai alla tua disposizione, è raggiungibile tramite il link 'Visualizzare lo spazio al server'. </li>
</ul>
<p><b>b) Gestione della lista degli utenti.</b> In questo modulo è possibile gestire la lista degli utenti nel corso. È possibile aggiungere uno o più utenti attraverso il link 'Aggiungere un utente' e 'Aggiungere molti utenti'. Puoi anche aggiungere un utente ospite, cliccando sul link 'Aggiungere un utente ospite'. Per cercare un utente fai clic su 'Cercare utente' e compila il suo nome e cognome o il username. Per cancellare un utente basta premere il pulsante (<img src='$themeimg/delete.png' width=16 height=16>) che si trova sul lato destro dei dati dell'utente. </p>";
$langGroup_studentContent = "<p>Un gruppo di utenti è una <b>collezione</b> di utenti registrati che condividono lo stesso forum e lo stesso spazio sul server di file e compiti caricati. Il modulo 'Gruppi di utenti' permette la collaborazione e l'interazione degli studenti attraverso la loro organizzazione in gruppi. In questo modo si rinforza l' apprendimento collaborativo, si sono risposte varie domande e scambiate delle opinioni su certe questioni del corso. I dati di un gruppo come: il manager del gruppo, il numero degli utenti registrati, il numero massimo degli utenti autorizzati nel gruppo è alla tua disposizione, per il tuo migliore aggiornamento riguardante al gruppo.</p>.";
$langHAgenda = "Agenda";
$langHAgenda_student = $langHAgenda ;
$langAgendaContent = "<p>Il modulo 'Agenda' ha l' obbiettivo  di <b>aggiornare</b> e <b>organizzare</b> il tempo degli insegnanti e degli studenti sui processi che hanno luogo in un certo corso. La specificazione dei capitoli (materia didattica), la specificazione del tempo quando avranno luogo la presentazioni del corso e dei soggetti che tratterà il corso, sono alcuni degli usi di questo modulo. Per aggiungere un evento all' agenda del corso, clicca sul link 'Aggiungere un evento '. Imposta il titolo, la descrizione, la data, l'ora e la durata che questo fatto venga postato. Per inserire dei simboli matematici segui le istruzioni che seguono. Il  processo viene completato premendo il link 'Aggiungere / Modificare'. </p> 
<p><b>Operazioni supportate dal modulo 'Agenda'</b><br><br> 
Le operazioni che sono supportate dalla piattaforma sul modulo 'Agenda' sono le seguenti: 
<ul> 
  <li>Modificare un evento. Per modificare un evento basta fare clic sul pulsante<img src='$themeimg/edit.png' width=16 height=16> che si trova sulla destra</li> 
  <li>Eliminare un evento. Per eliminare un evento basta fare clic sul pulsante (<img src='$themeimg/delete.png' width=16 height=16>) che si trova sulla destra</li> 
  <li>Visibile / Invisibile. Per rendere visibile / invisibile (e viceversa) un evento basta premere il pulsante <img src='$themeimg/visible.png' width=16 height=16> che si trova sulla destra.</li>
  <li>Invertire l' ordine della visualizzazione degli eventi. Puoi invertire l'ordine della visualizzazione scegliendo tra l' evento più recente a quello più vecchio e viceversa,  cliccando sul link 'Invertire l' ordine di visualizzazione'. </li> 
</ul> 
<hr noshade size=1>
<p> $langPHPMathPublisher </p>
";
$langAgenda_studentContent = "
<p>Il modulo 'Agenda' ha l' obbiettivo  di <b>aggiornare</b> e <b>organizzare</b> il tempo degli studenti sui processi che hanno luogo in un certo corso. La specificazione dei capitoli (materia didattica), la specificazione del tempo quando avranno luogo la presentazioni del corso, dei soggetti che tratterà il corso, sono alcuni degli usi di questo modulo. Usando la 'barra di scorrimento' sul lato destro del tuo browser, si possono vedere i vari eventi che si svolgono in un certo corso. È importante notare che oltre alla data programmata  di un evento, si riferisce ancora  l'ora esatta della sua realizzazione. L'utilità di questo modulo è evidente soprattutto in caso di emergenze o del cambiamento dell' orario della realizzazione di un corso.
</p> 
" ;
$langHLink = "Links";
$langHLink_student = $langHLink;
$langLinkContent = "<p>Via il modulo 'Link' puoi raccogliere tutti i link che sono utili per il corso. La piattaforma offre la possibilità di sistemare i link in categorie per facilitare il loro accesso. Le caratteristiche di questo modulo sono:</p>
<p> <b>a)  Aggiungere link.</b> Per aggiungere un nuovo link, basta premere 'Aggiungere link'. Nel display visualizzato, inserisci l' indirizzo di una risorsa (un sito) sul Web (Uniform Resource Locator), il suo nome, una leggenda e la categoria a cui appartiene. Il processo è completato premendo sul link 'Aggiungere'</p> 
<p> <b>b) Aggiungere categoria.</b> Per creare una nuova categoria, fai clic su 'Aggiungere categoria '.  Nel display visualizzato, compila il nome della categoria e una descrizione. Il processo viene completato, cliccando sul link 'Aggiungere'. </p> <hr> 
<p>Le operazioni che sono supportati dalla piattaforma sul modulo 'Link' sono: </p> 
<ul> 
<li>Modificare un link. Per modificare un link, basta cliccare il pulsante <img src='$themeimg/edit.png' width=16 height=16> che si trova a destra</li> 
<li> Elimina un link. Per eliminare un link, basta premere il pulsante (<img src='$themeimg/delete.png' width=16 height=16>) che si trova a destra</li> 
<li> Spostare un link. Per cambiare l' organizzazione dei link in categorie, utilizza i pulsanti (<img src='$themeimg/up.png' width=16 height=16>) e (<img src='$themeimg/down.png' width=16 height=16>) che si trovano sulla destra</li> 
</ul> 
";
$langLink_studentContent = "<p>Il modulo link ti dà la possibilità di accedere a risorse utili su Internet raggruppate in categorie correlate al corso. Questi link sono scelti dall' insegnante del corso. Ogni link ha un titolo, una descrizione, con ulteriori informazioni. La piattaforma favorisce la <b>classificazione</b> dei link, rendendoli più facili da accederli. </p>";
$langHAnnounce = "Annunci";
$langHAnnounce_student = $langHAnnounce ;
$langAnnounceContent = "<p>Puoi aggiungere degli annunci nella pagina del corso cliccando sul link 'Aggiungere Annuncio'. Compila nel modulo visualizzato il titolo e il corpo dell' annuncio. Per inserire dei simboli matematici effettua le operazioni che seguono:
<p>Attraverso le icone <img src='$themeimg/edit.png' width=16 height=16> (Modificare) e <img src='$themeimg/delete.png' width=16 height=16> (Eliminare) puoi raggiungere le funzioni corrispondenti di un annuncio. L' attivazione-disattivazione del modulo degli annunci si realizza cliccando sui link 'Attivazione' - 'Disattivazione'  che stanno a destra del titolo del modulo.</p> 
<p>L' icona <img src='$themeimg/feed.png' width=16 height=16> abilita il ricevimento automatico degli annunci del corso attraverso il RSS (Really Simple Syndication) sia da un telefono cellulare che da un computer. Basta premere sull' icona e se hai un computer o telefono cellulare, fornito di un lettore RSS, sarai in grado di approfittarti dei vantaggi di questo servizio. </p>
<hr noshade size=1>
<p> $langPHPMathPublisher </p>
<p><a name='syn6'></a>
";
$langAnnounce_studentContent = "<p>Il modulo 'Annunci' permette l' <b>aggiornamento</b> degli studenti su temi che riguardano il corso. La piattaforma supporta il tuo aggiornamento con gli annunci del corso in tre modi: 
</p> 
<ol> 
<li><b>Segui</b>regolarmente il modulo 'Annunci' e segui i post che sono creati dall' insegnante del corso.</li> 
<li>Se hai dichiarato, durante la tua iscrizione al corso, un indirizzo <b>attivo</b> di e-mail, puoi ricevere i post del modulo degli annunci. Se non hai dichiarato un indirizzo e-mail, puoi farlo modificando il tuo profilo ed inserendo un valido indirizzo e-mail. </li> 
<li><b>Aggiornamenti<b> per i post al tuo computer o il tuo telefono cellulare  attraverso il servizio RSS <img src='$themeimg/feed.png' width=16 height=16>. Questa è la possibilità di ricevere automaticamente gli annunci del corso attraverso il RSS (Really Simple Syndication) sia al telefono cellulare che al computer. Basta premere sull'icona e avere un' applicazione di tipo RSS Reader (lettore RSS), per poter approfittarsi dai privilegi di questo servizio.</li>
</ol>
<p>
</p>";
$langHProfile = "Modificare profilo";
$langProfileContent = "<p>Con questo modulo puoi <b>modificare</b> i dati di in un utente della piattaforma. È possibile modificare il nome/cognome dell'utente e il username. Inoltre è possibile modificare l' indirizzo email di un utente ed optare se sarà privato, apparirà a tutti o solo agli insegnanti. Il numero di immatricolazione e il numero telefono si possono pure modificare o cambiare la visibilità. Se ti interessa l' aspetto del tuo corso, puoi specificare il modo di visualizzazione,  selezionando 'Dettagliato' o 'Abbreviato'. Se desideri o non ricevere e-mail dai tuoi corsi, semplicemente seleziona la corrispondente casella di spunta. È possibile anche specificare la facoltà – dipartimento tramite una lista. Da raggiungere una forma completa del profilo è possibile aggiungere una foto e una breve descrizione. Il processo si conclude facendo clic su 'Modificare'.</p>
";
$langHModule = "Aggiungere link esterno";
$langModuleContent = "<p>Se desideri aggiungere dei link dalla home page del corso a siti che già esistono altrove sulla rete (o anche da qualche parte sul tuo sito) digita il link e il suo titolo e poi premi 'Aggiungere'. Le pagine che aggiungi alla home page possono essere disattivate e cancellate, ma gli strumenti incorporati possono solo essere disattivati, ma non cancellati.</p>";
$langHcourseTools = "Attivare strumenti";
$langcourseToolsContent = "
<p>Attraverso questo modulo puoi <b>attivare</b> o <b>disattivare</b> i vari moduli del corso. Nelle due colonne si visualizza lo stato di ciascun modulo, cioè se è attivo o non.<br> 
Per cambiare lo stato di un modulo, seleziona la colonna in cui si trova e corrisponde allo stato attivato/disattivato e premi il pulsante (<img src='$themeimg/arrow.png' width=16 height=16>). Se desideri cambiare lo stato a più di uno moduli, barra selezionarli nella collonna corrispondente facendo CTRL-Clic, premere il pulsante (<img src='$themeimg/arrow.png' width=16 height=16>) e muoverli da una colonna all' altra. Tutte questi processi si completano premendo il link 'Salvare Modifiche'. </p> 
<hr> 
<p><b>Operazioni supportate del modulo 'Attivazione di strumenti'</b><br><br> 
Le operazioni supportate dalla piattaforma sul modulo dell' attivazione degli strumenti sono come segue: 
<ul> 
<li> <b>Caricare pagina Web</b>. Questa operazione funziona come un link su una pagina (in formato HTML) che hai costruito e riguarda il corso attuale. Così, viene visualizzata agli strumenti del corso.</li> 
<li> <b>Aggiungere un link esterno sul menu laterale di sinistra</b>. Se desideri inserire un link esterno, compila l' indirizzo del link e premi il pulsante 'Aggiungere'. </li> 
 </ul> 
</p>";
$langHInfocours = "Gestione di Corso";
$langHConference = "Collaborazione remota";
$langHConference_student = $langHConference ;
$langConferenceContent = "<p>Il modulo 'Collaborazione Remota', in forma semplice, favorisce lo scambio di opinioni e la comunicazione in tempo reale nell' ambito del corso. La comunicazione si effettua con testo normale (plaintext). Per inserire un messaggio alla collaborazione remota, basta digitare il tuo messaggio e premere il pulsante (<img src = '$themeimg/arrow. png' width=16 height=16>). </p>
<ul>
<li><b>Svuotare</b>. Cliccando su questo link si puoi <b>svuotare</b> l'area di messaggi della 'Collaborazione Remota', sia quando il volume dei messaggi è molto grande, che quando inizi un nuovo tema di 'Collaborazione Remota'</li>
<li><b>Salvare</b>Cliccando questo link, puoi salvare i messaggi che sono stati scambiati nell' area della 'Collaborazione Remota'. I messaggi vengono salvati nel menu 'Documenti' (in formato .txt) e sono visibili agli studenti. Se sei un insegnante, puoi renderli invisibili facendo clic sul pulsante <img src='$themeimg/visibile.png' width=16 height=16>.</li> 
</ul> 
";
$langConference_studentContent = "<p>Il modulo 'Collaborazione Remota', in forma semplice, favorisce lo scambio di opinioni e la comunicazione in tempo reale nell' ambito del corso. La comunicazione si effettua con testo normale (plaintext). Per inserire un messaggio alla collaborazione remota, basta digitare il tuo messaggio e premere il pulsante (<img src = '$themeimg/arrow. png' width=16 height=16>). </p>
";
$langHVideo = "Multimedia";
$langHVideo_student = $langHVideo ;
$langVideoContent = "<p>
Una parte del materiale dei corsi potrebbe essere del materiale audiovisivo.
Il modulo 'Multimedia' può servire a migliorare il process dell'insegnamento e dell' apprendimento. Attraverso l' animazione e il suono che offre un file multimediale lo studente può sviluppare le abilità che sono difficili da creare attraverso un materiale stampato. Per pubblicare un file multimediale la piattaforma impiega due modi: </p> 
<p> 
<b>a) </b> Il link 'Aggiungere file multimediale'. Seleziona  'Aggiungere file multimediale' ed imposta il percorso dove il file è archiviato sul tuo computer scegliendo 'Sfoglia'. 
Compila i campi del titolo, descrizione, autore, editore, e data e fai clic su 'Caricare File' per completare il processo.
</br></br> 
<b> b)</b> Il link 'Aggiungere un link video'. Seleziona 'Aggiungere un link video' e fornisci l' URL (Uniform Resource Locator), cioè l'indirizzo della tua risorsa sul Web, che in questo caso è il tuo video. Un esempio di un URL è http://www.youtube.com/video1.wmv. Compila i campi: titolo del video, descrizione, autore, editore, la data del video e premi il pulsante 'Aggiungere' per completare il processo.</p> 
<hr> 
<p><b>Operazioni supportate dal modulo 'Multimedia'</b><br><br> 
Le operazioni che sono supportate dalla piattaforma sul modulo 'Multimedia' sono come segue: 
<ul> 
<li> Modificare le informazioni. Per modificare alcune informazioni sul file multimediale basta premere il pulsante <img src='$themeimg/edit.png' width=16 height=16> che si trova a destra</li> 
<li> Eliminare. Per eliminare un file multimediale, basta cliccare sul pulsante (<img src='$themeimg/delete.png' width=16 height=16>)  che si trova a destra</li> 
<li> Visualizzare lo spazio al server. Questa operazione ti consente di visualizzare lo spazio al server occupato rispetto al totale disponibile.</li> 
</ul> 
";
$langVideo_studentContent = "<p>
Una parte del materiale dei corsi potrebbe essere del materiale audiovisivo.
Il modulo 'Multimedia' può servire a migliorare il processo dell'insegnamento e dell' apprendimento. Attraverso l' animazione e il suono che offre un file multimediale lo studente può sviluppare le abilità che sono difficili da creare attraverso un materiale stampato. 
Per riprodurre un file multimediale, basta cliccare 'su' di questo.
La riproduzione si raggiunge <b>dal browser</b> stesso che usi per la navigare su Internet, o tramite un link a una pagina Web <b>esterna</b>.
Se desideri scaricare un file multimediale sul tuo computer da salvarlo, clicca sul pulsante <img src='$themeimg/save_s.png' width=17 height=17> (salvare) che si trova sulla destra. Tali file sono utilizzati per trasmettere informazioni audiovisive e creare condizioni efficaci di interazione tra gli studenti e il contenuto formativo.
Combinando il modulo 'Multimedia' con il modulo 'Documenti' risulta uno 'strumento di apprendimento' <b>potente</b> che è disponibile allo studente.
</p> ";
$langHCoursedescription = "Descrizione del corso";
$langHCoursedescription_student = $langHCoursedescription ;
$langCoursedescriptionContent = "<p>Attraverso il modulo 'Descrizione del Corso' puoi aggiungere delle informazioni cliccando sul link 'Creare e Modificare'. Le aree in cui puoi aggiungere delle informazioni si dividono nelle categorie seguenti: 
<ul>
   <li>Contenuto del corso</li>
   <li>Attività formative</li>
   <li>Ausiliari</li>
   <li>Risorse Umane</li>
   <li>Modi di valutazione / esame </ li>
   <li>Informazioni aggiuntive</li>
</ul>
<p>Puoi aggiungere una categoria selezionandola dalla lista delle categorie esistenti e poi cliccando sul link 'Aggiungere'.
Compila la descrizione della categoria selezionata e premi sul pulsante 'Aggiungere', per fissare la nuova categoria. In caso contrario clicca su 'Annullare e Ritornare'.

<p><img src='$themeimg/warning.png' width=18 height=18> Su questo modulo devi tener conto dei seguenti punti: 
<ul> 
<li>La descrizione del corso si può cambiare anche dalla Home Page del corso cliccando sul pulsante  <img src='$themeimg/edit.png' width=16 height=16> che si trova sotto il titolo del corso.</li> 
<li>Le categorie 'Contenuto del corso', 'Attività formative', 'Ausiliari', 'Risorse Umane', 'Modi di valutazione / esame' possono essere utilizzate una sola volta nell'ambito del corso, invece la categoria 'Informazioni aggiuntive', <b>tante</b> volte.</li> 
<li>Attraverso il processo 'Selezionare e Modificare' e selezionando la categoria 'Informazioni aggiuntive', puoi sostituire il titolo della categoria selezionata con una nuova, sicché tu definisca una nuova categoria personalizzata.</li> 
</ul> 
</p> 

<hr> 
<p><b> Operazioni Supportate dal modulo 'Descrizione del Corso'</b><br><br>
Le operazioni che sono supportate dalla piattaforma sul modulo  'Descrizione del Corso' sono le seguenti: 
<ul> 
<li>Modificare. Per modificare alcuni parametri sulle categorie suddette, basta fare clic sul pulsante <img src='$themeimg/edit.png' width=16 height=16> che si trova sulla destra</li> 
<li>Eliminare. Per eliminare alcuni parametri sulle categorie suddette, basta fare clic sul pulsante (<img src='$themeimg/delete.png' width=16 height=16>) che si trova sulla destra.</li> 
<li>Aggiungere categoria alla Home Page del corso. Puoi aggiungere una delle categorie suddette alla Home Page del corso, cliccando sul pulsante <img src='$themeimg/publish.png' width=16 height=16>.</li >
</ul >
<hr>
<p>
$langPHPMathPublisher";
$langCoursedescription_studentContent = "<p>Se il responsabile insegnante  <b>crea</b> la descrizione del corso, allora in questo modulo si possono trovare delle informazioni utili riguardanti l'identità del corso. In questo modo attraverso il modulo 'Descrizione del corso', uno può conoscere le diverse zone del corso. Categorizzando queste zone, ci sono: </p>
<ul>
   
   <li>Contenuto del corso</li>
   <li>Attività formative</li>
   <li>Ausiliari</li>
   <li>Risorse Umane</li>
   <li>Modi di valutazione / esame </ li>
   <li>Informazioni aggiuntive</li>
</ ul>
<p>Tutte queste zone sono state progettate per raggiungere il tuo migliore aggiornamento e la tua migliore organizzazione ai corsi che stai attualmente seguendo.</p";
$langHPath = "Guida - Linea d' apprendimento";
$langHPath_student = $langHPath ;
$langPathContent = "<p>Lo strumento 'Percorso di apprendimento' (learning path) supporta quattro operazioni principali:</p>
<ul>
<li>Creare percorso di apprendimento</li>
<li>Inserire percorso di apprendimento dal modello SCORM o IMS</li>
<li>Esportare percorso di apprendimento al modello compatibile con SCORM 2004 o 1.2</li>
<li>Monitorizzare gli studenti sui percorsi di apprendimento</li>
<ul>
<hr>
<p><b>Che cosa significa percorso di apprendimento?</b></p>
<p>Il percorso di apprendimento è una sequenza di passi di apprendimento compresi in sezioni.
Può essere basata sia al contenuto (assomiglia a un sommario),
che ad azioni, assomigliando a un' agenda o un programma di azioni che vanno realizzate dallo studente, affinché lui comprenda o si addestri in un certo soggetto.
Oltre ad essere strutturata, un percorso di apprendimento può anche avere una certa successione di passi. Ciò significa che alcuni passi sono presupposti per
quelli immediatamente seguenti ('non si può procedere al passo 2 prima del passo 1').
La successione può essere solo indicativa (i passi si visualizzano l' uno dopo
l'altro). </p>
<hr>
<p>Come puoi creare il tuo percorso di apprendimento?</b></p>
<p>Il primo passo è quello di accedere al settore 'Lista di percorsi di apprendimento'. Nel display principale della
lista di percorsi di apprendimento, c' è un link speciale. Ci puoi
creare dei percorsi di apprendimento a volontà, cliccando sul  link
<i>'Creare nuovo percorso di apprendimento'</i>. In questo modo si creano vuoti
percorsi di apprendimento, in cui puoi aggiungere delle sezioni e dei passi. </p>
<hr>
<p><b>Quali sono i passi per questi percorsi ? (quali sono gli oggetti che ci possono essere aggiunti ?)</b></p>
<p>Alcuni strumenti, operazioni e contenuti della piattaforma Open eClass, che consideri utili
ed opportuni per il tuo percorso di apprendimento, possono essere aggiunti:</p>
<ul>
<li>Documenti  (testi, immagini, documenti di formato Office, ...) </li>
<li>Leggende</li>
<li>Link</li>
<li>Esercizi della piattaforma Open eClass</li>
<li>Descrizione del corso</li>
<ul>
<hr>
<p><b>Altre caratteristiche del percorso di apprendimento</b></p>
<p>E possibile che agli studenti sia chiesto di seguire (leggere)
il tuo percorso in un ordine specifico. Ciò significa ad esempio che
gli studenti non sono autorizzati ad accedere al Esercizio 2, se non hanno letto
il Documento 1. Tutti gli oggetti hanno uno stato: completo o incompleto,
quindi il progresso degli studenti è sempre disponibile attraverso lo 
strumento speciale <i>Monitorizzare i percorsi dell'apprendimento</i>.</p>
<p>Se desideri modificare il titolo originale di un passo, il nuovo titolo
si può visualizzare sul percorso, senza smuovere l'originale. Quindi, se vuoi
rendere visualizzato il test8.doc  come 'Esame Finale' sul percorso, non è necessario di
rinominare il file, basta utilizzare un altro titolo sul percorso.

Si propone inoltre di fornire nuovi titoli sui link se loro hanno un nome lungo. </p>
<br>
<hr>
<p><b>Che cosa significa il percorso di apprendimento secondo il modello SCORM o IMS e come si può inserire?</b></p>
<p>Lo strumento del percorso di ​​apprendimento ti consente di caricare ed inserire
del contenuto educativo, compatibile con il modello SCORM ed IMS.</p>
<p>Lo SCORM (<i>Sharable Content Object Reference Model</i>) è un modello internazionale, 
il quale hanno adottato molte ditte preminenti e orientate alla formazione a distanza asincrona (e-Learning), 
come ad esempio: NETg, Macromedia, Microsoft, SkillSoft, ecc. e che si opera in tre settori: </p>
<ul>
<li><b>Economia</b>: Il modello SCORM consente a corsi interi o
sezioni minori di contenuti di riutilizzarsi in piattaforme di
E-learning (Learning Management Systems - LMS) diverse, attraverso
la separazione tra il contenuto e la struttura, </li>
<li><b>Pedagogia</b>: Il modello SCORM incorpora i concetti dei
prerequisiti o della <i>successione</i> (<i>per esempio</i> 'non
puoi accedere al Capitolo 2, se non completi con successo l' Esercizio 1'),</li>
<li>Tecnologia</b>: Il modello SCORM compone un sommario 
come un ulteriore livello di astrazione, indipendentemente dai contenuti e della piattaforma E-Learning.
Aiuta che i concetti del contenuto e della piattaforma E-learning
si comunichino fra loro. Questa comunicazione si realizza 
principalmente con <i>indici</i> ('Dove sta Gianni di preciso sulla materia del corso ?'), 
<i>punteggio</i> ('Quale voto ha Gianni preso sull' esercizio ?'),  e <i>tempo</i>
('Quanto tempo ha Gianni speso sul Capitolo 1' ?). <li>
<ul>

<hr>
<p><b>Come si crea un percorso di apprendimento compatibile con il modello SCORM ?</b></p>
<p>Il metodo più naturale è quello di utilizzare lo strumento 'Creare percorso di apprendimento'
della piattaforma Open eClass, e quindi estrarlo cliccando l' apposita
icona. Tuttavia, è possibile creare del contenuto didattico
compatibile con il modello SCORM sul tuo computer locale e poi importarlo nello strumento 'Percorso di apprendimento' dell' Open eClass. 
In questo caso, sei consigliato di utilizzare uno strumento sofisticato come
Lectora ® o Reload ® </p>
<hr>
<p><b>Link utili</b></p>
<ul>
<li>Adlnet: l'autorità responsabile per la normalizzazione del modello SCORM, <a
href='http://www.adlnet.org/'>http://www.adlnet.org</a></li>
<li>Reload: strumento di Free Software / Open Source Software per la preparazione e
la lettura di contenuto SCORM, <a href='http://www.reload.ac.uk/'>http://www.reload.ac.uk</a></li>
<li>Lectora: strumento per la preparazione e la pubblicazione di contenuti SCORM, <a
href='http://www.trivantis.com/'>http://www.trivantis.com</a></li>
<ul>
<b><p>Nota:</b></p>
<p>Il settore Lista di percorsi di apprendimento visualizza tutti i percorsi di apprendimento
che <i>sono stati creati attraverso l' Open eClass</i> e tutti i percorsi di apprendimento importati e 
<i>compatibile con il modello SCORM</i>.</p>
<p>Il link 'Attivare / Disattivare' rende il percorso di apprendimento selezionato uno strumento attivo o inattivo  e viceversa.</p>
";
$langPath_studentContent = "<p>Il percorso di apprendimento (learning path) è una sequenza di passi di apprendimento divisi in sezioni.
Può essere basata sia al contenuto (assomiglia a un sommario),
che ad azioni (assomigliando a un' agenda) o a un programma di azioni che vanno realizzate dallo studente, affinché lui comprenda un certo soggetto.
Oltre ad essere strutturata, un percorso di apprendimento può anche avere una certa successione di passi. Ciò significa che alcuni passi sono <b>presupposti</b> per
quelli immediatamente seguenti ('non si può procedere al passo 2 prima del passo 1').
La successione può essere solo indicativa (i passi si visualizzano l' uno dopo
l'altro). Tutti gli oggetti del percorso di apprendimento hanno uno stato: completo o incompleto,
quindi il progresso degli studenti è sempre disponibile attraverso lo 
strumento speciale 'Monitorizzare i percorsi dell'apprendimento'. Per monitorizzare un oggetto didattico di un percorso di apprendimento, clicca sul titolo del percorso. Scegli tra gli oggetti visualizzati quale vuoi monitorizzare (attenzione: ad alcuni ha un ruolo la loro classifica). A seconda delle azioni che si svolgono (monitoraggio delle diapositive) si cambiano le percentuali dell' indice di progresso (a destra). Cliccando sul pulsante<img src='$themeimg/monitor.png'width=16 height=16> puoi visualizzare le informazioni dettagliate dell' Oggetto Didattico come: il Titolo, i Commenti, il Tipo della sezione, il tempo totale ecc.</p>";
$langHDropbox = "Area di Condivisione di File";
$langHDropbox_student = $langHDropbox ;
$langDropboxContent = "<p>Il modulo 'Dropbox' (Area di Scambio File) è uno strumento che serve allo scambio di file tra insegnante e studente. Puoi scambiare qualsiasi tipo di file conosciuti (doc, docx, odt, pdf, ecc).</p> 
<p>Il modulo include due cartelle condivise:</p> 
<p><b><u>File in arrivo</u></b>. In questa cartella si visualizzano tutti i file che hai ricevuto dagli studenti con alcune informazioni ulteriori sul file, come: il nome dell' utente, la dimensione del file e la data d' invio. Per eliminare un file in arrivo, basta cliccare sull'icona (<img src='$themeimg/delete.png' width=10 height=10>) che sta sulla stessa riga con il file che vuoi rimuovere.</p> 
<p><b><u>File inviati</u></b>. In questa cartella si visualizzano i file che hai inviato a tutti gli utenti registrati nel corso (studenti, insegnanti) della piattaforma insieme con le informazioni corrispondenti. Per inviare un file ad un utente, devi prima cliccare sul link 'Caricare file'. In questo punto devi specificare il percorso dove il file si trova sul tuo computer scegliendo 'Sfoglia', e i suoi destinatari. Il campo 'Breve Descrizione' è facoltativo e lo puoi omettere. Via la  casella di controllo 'Notificare agli utenti via e-mail',  puoi notificare agli utenti l' invio del file specifico tramite una posta elettronica.</p>
<p><img src='$themeimg/warning. png' width=18 height=18> In questo modulo devi tener conto dei seguenti punti: 
<ul> 
<li>Lo spazio totale delle tue cartelle è concreto e può essere impostato solo dal loro gestore</li> 
<li>Per motivi di sicurezza i file eseguibili del formato *.exe non vanno 'caricati' sulla piattaforma</li> 
<li>Quando si elimina un file, ciò non viene eliminato dalla database della piattaforma, ma solo dalla cartella attuale</li> 
</ul> 
</p> 
";
$langDropbox_studentContent = "<p>Il 'Dropbox' (Area di Scambio File) è uno strumento che serve allo scambio di file tra insegnante e studente. Puoi scambiare qualsiasi tipo di file conosciuti (doc, docx, odt, pdf, ecc).</p><p>Il modulo include due cartelle condivise:</p> 
<p><b><u>File in arrivo</u></b>. In questa cartella si visualizzano tutti i file che hai ricevuto dall' insegnante o da un altro studenti registrato nel corso. Alcune informazioni ulteriori sul file, come: il nome dell' utente, la dimensione del file e la data d' invio, sono dei dati molto utili per il tuo aggiornamento. Per eliminare un file in arrivo, basta cliccare sull' icona (<img src='$themeimg/delete.png' width=10 height=10>) che sta sulla stessa riga con il file che vuoi rimuovere.</p> 
<p><b><u>File inviati</u></b>. In questa cartella si visualizzano i file che hai inviato a tutti gli utenti registrati nel corso (studenti, insegnanti) della piattaforma insieme con le informazioni corrispondenti. Per inviare un file ad un utente, devi prima cliccare sul link 'Caricare file'. In questo punto devi specificare il percorso dove il file si trova sul tuo computer scegliendo 'Sfoglia', e i suoi destinatari. Il campo 'Breve Descrizione' è facoltativo e lo puoi omettere. Via la casella di controllo 'Notificare agli utenti via e-mail',  puoi notificare agli utenti l' invio del file specifico tramite una posta elettronica.</p>
<p><img src='$themeimg/warning. png' width=18 height=18> In questo modulo devi tener conto dei seguenti punti: 
<ul> 
<li>Lo spazio totale delle tue cartelle è concreto e può essere impostato solo dal loro gestore</li> 
<li>Per motivi di sicurezza i file eseguibili del formato *.exe non vanno 'caricati' sulla piattaforma</li> 
<li>Quando si elimina un file, ciò non viene eliminato dalla database della piattaforma, ma solo dalla cartella attuale</li> 
</ul> 
</p>";
$langHUsage = "Statistiche di utilizzo";
$langUsageContent = "<p>Questo modulo serve a visualizzare le statistiche per quanto riguarda entrambi i corsi e gli utenti. Attraverso diagrammi e liste di valori, hai alla tua disposizione vari <b>utili</b> elementi da cui puoi trarre una conclusione per quanto riguarda il corso. Operazioni come le 'Visite degli utenti a ogni corso' possono risultare molto utili per formulare un parere sull' interesse che suscita un corso agli studenti.</p>
<p><b>Le <b>categorie</b> delle statistiche che sono supportate dalla piattaforma sono le seguenti:</p>
<ul>
<li><b>Preferenza dei moduli</b>. Su questa operazione, si visualizzano le preferenze dell'utente per i vari elementi della piattaforma. L' impostazione dei parametri (data di inizio, data di scadenza, ecc) che saranno utilizzati per la ricerca delle statistiche, si svolgerà in dettaglio alla sezione 'Preferenza dei moduli'.</li>
<li><b>Visite degli utenti per corso</b>. Questa operazione permette la visualizzazione dei dati degli utenti che hanno accesso al corso. Tra gli altri, si visualizzano il suo username, il suo indirizzo (indirizzo IP), e la data della sua entrata (login).</li>
<li><b>Partecipazione degli utenti per corso</b>. Attraverso questa opzione,  puoi visualizzare i dati degli studenti che appartengono ad un gruppo di un corso. Tra gli altri, si visualizzano il  nome e cognome dell' utente, il suo numero di matricolazione (riguarda gli studenti), il gruppo di utenti al quale appartiene, e la durata di navigazione sulla piattaforma.</li>
<li><b>Percorsi di apprendimento</b>. Attraverso questa operazione, puoi monitorizzare il progresso di uno studente sui percorsi di apprendimento. Elementi come: il nome dello studente, il numero di matricolazione, il gruppo di utenti a cui appartiene e la visualizzazione del suo progresso (in percentuale) è alla tua disposizione per trarre conclusioni </li>.
<li><b>Statistiche di gruppi di utenti</b>. Informazioni utili per i gruppi di utenti vengono visualizzate in questa sezione. Il nome del gruppo, il suo capo, il numero degli utenti registrati, e il numero massimo degli che ci si possano registrare, sono degli elementi che aiutano alla migliore organizzare dei gruppi del corso.</li>
</ul>
<hr>
<p>Preferenza dei moduli</b></p>
<p>La preferenza dei moduli<p> è un' operazione che è inclusa in tutte le suddette categorie di statistiche, e permette l' impostazione dei parametri che saranno utilizzati alla ricerca delle statistiche. Prima puoi specificare il tipo delle statistiche, in base al 'Numero di visite' o la 'Durata'. Poi, specifica la data di inizio e di scadenza, e prosegui alla visualizzazione delle statistiche. Se desideri visualizzare le statistiche per un certo utente, digita la prima lettera del suo cognome, altrimenti seleziona 'Tutti gli utenti' dalla lista di valori. Il processo si completa cliccando sul pulsante 'Inviare'.</p>";
$langHCreateCourse = "Creare corso";
$langCreateCourseContent = "
<p>Questo modulo è uno strumento molto importante della piattaforma, perché  l' insegnante lo può utilizzare per creare nuovi corsi. Il processo di creazione è costituito da 3 passi. In particolare:</p> 
<p><b>Passo 1:</b> All' inizio compila i dati fondamentali e le informazioni del corso. Questi riguardano al titolo del corso, la scuola-facoltà a cui appartiene (si sceglie da una lista di valori), l'insegnante del corso, il tipo (laurea / post laurea) e infine la lingua in cui apparirà questo corso.<br><br>
<img src='$themeimg/warning.png' width=18 height=18>
<b>Nota:</b> Se lasci alcuni campi vuoti, il sistema te ne avverte e non puoi procedere al passo successivo.</p>
<p><b>Passo 2:</b> In questo passo compila delle informazioni aggiuntive sul corso. Queste contengono una breve descrizione del corso e alcune parole chiave che aiuteranno lo studente comprendere al meglio il materiale didattico.</p> 
<p><b>Passo 3:</b> Accedere al corso e attivare gli strumenti. In questo passo puoi impostare il modo d' accesso degli utenti al corso.
La piattaforma supporta i seguenti modi di accesso da parte degli utenti:</p> 
$langCourseAccessHelp 
";
$langHWiki = "Wiki";
$langHWiki_student = $langHWiki ;
$langWikiContent = "<p>Attraverso il modulo <b>Wiki</b> gli studenti e gli insegnanti possono  creare documenti in collaborazione. Questa operazione viene effettuata utilizzando un browser. Per questo motivo,  i <b>Wiki</b> sono uno strumento utile per la collaborazione tra gruppi di studenti nell' ambito del corso.</p>
<p>Per creare un nuovo <b>Wiki</b> basta fare clic sul link 'Creare un nuovo Wiki'. Nel display visualizzato, compila il titolo del Wiki, inserisci una descrizione e fai clic sul pulsante 'Salvare' o 'Annullare' per procedere all' operazione rispettiva.</p>
<p>Una serie di operazioni sono alla tua disposizione per quanto riguarda il Wiki. In particolare:</p>

<ul>
<li><b>Ultime modifiche.</b> Con questa opzione puoi osservare le ultime modifiche effettuate al wiki. Operazioni come <img src='$themeimg/wiki.png' align='absmiddle'> la visualizzazione della Home Page di Wiki, <span class='fa files-o'></span>> la visualizzazione di tutte le pagine , <span class='fa list'></span> la visualizzare dell'elenco delle pagine di Wiki sono disponibili tramite questo modulo. </li>
<li><b>Cambiare impostazioni.</b> <img align='absmiddle' src='$themeimg/edit.png'> Puoi modificare il titolo e la descrizione di un certo Wiki con questa opzione.</li>
<li><b> Eliminare.</ b> Per eliminare un Wiki basta premere il pulsante (<img src='$themeimg/delete.png' width=16 height=16>), che sta a destra </li >
</ul>

$langWikiSyntaxHelp";
$langWiki_studentContent = "
<p>Attraverso il modulo <b>Wiki</b> gli studenti e gli insegnanti possono  creare documenti in collaborazione. Questa operazione viene effettuata utilizzando un browser. Per questo motivo,  i <b>Wiki</b> sono uno strumento utile per la collaborazione tra gruppi di studenti nell' ambito del corso.</p>
<p>Una serie di operazioni sono alla tua disposizione per quanto riguarda il Wiki. In particolare:</p>

<ul>
<li><b>Ultime modifiche.</b> Con questa opzione puoi osservare le ultime modifiche effettuate al wiki. Operazioni come <img src='$themeimg/wiki.png' align='absmiddle'> la visualizzazione della Home Page di Wiki, <span class='fa files-o'></span> la visualizzazione di tutte le pagine , <span class='fa list'></span> la visualizzare dell'elenco delle pagine di Wiki sono disponibili tramite questo modulo. </li>

$langWikiSyntaxHelp";
$langHGlossary = "Glossario";
$langHGlossary_student = $langHGlossary;
$langGlossaryContent = "
<p>L' insegnante del corso ha la possibilità di aggiungere e gestire i termini che sono inclusi nel glossario del corso. Dopo aver entrato nel menu del corso, seleziona il link 'Glossario' che si trova sulla parte sinistra della pagina. Puoi optare se desideri, che i termini si visualizzino nelle pagine del corso, cliccando su 'Impostazioni' sulla parte destra del Glossario, e poi cliccando sulla casella con l' etichetta 'Visualizzare le definizioni nelle pagine del corso'.  Clicca su 'Inviare' e il processo viene completato con successo. Sullo schermo viene visualizzato la notifica rispettiva.</p> 
<p>Una serie di operazioni è disponibile sul modulo 'Glossario'.</p> 
<hr> 
<p><strong>Inserire un termine nel Glossario</strong></br>
Per inserire un nuovo termine nel Glossario: 
<ul>
<li>Seleziona il link 'Aggiungere Termine' che si trova sulla parte destra della pagina. Nel modulo vuoto che viene visualizzato inserisci il termine e la definizione del termine</li> 
<li>Compila il termine, la spiegazione e l' URL ai campi corrispondenti</li> 
<li>Clicca su 'Inviare' per salvarlo</li> 
</ul> 
<hr> 
<p><strong>Gestire i termini del Glossario</strong><br/> 
Dopo aver aggiunto un termine, puoi modificarlo o eliminarlo dall' elenco. La selezione dell' operazione può essere fatta dai link corrispondenti che si trovano nel sotto-menu «Operazioni».</p><br/> 
<hr> 
<p><strong>Eliminare un termine</strong><br/> 
Per eliminare un termine, basta cliccare sul pulsante (<img src='$themeimg/delete.png' width=16 height=16>) e visualizzare una finestra da convalidare l' eliminazione.  Premendo 'OK' il termine si elimina con successo e sullo schermo viene indicato il messaggio informativo rispettivo.</p>
<br/> 
<hr> 
<p><strong>Scaricare i termini del Glossario in formato csv</strong></br> 
Il gestore del corso elettronico può pure scaricare tutti i termini del corso inclusi nel Glossario in formato csv (comma seperated values - valori separati di virgola).
 Il termine 'comma-separated values' riguarda ad un insieme di formati di file che sono utilizzati per memorizzare i dati da una tabella di valori (testo, numeri) a un formato di testo semplice che può essere letto da un editor di testo. Le righe nel file di testo rappresentano le righe di una tabella, e le virgole in una riga separano i campi nelle righe della tabella.
  Per scaricare i termini del Glossario in formato csv, imposta la codificazione desiderata selezionando tra UTF8 e Windows 1253.  Con questa opzione, il browser che utilizzi ti mostrerà un messaggio con cui ti indica di aprire il file direttamente, o di dare il percorso in cui sarà salvato il file.</p>
";
$langGlossary_studentContent = "
<p>Si tratta di un modulo che presenta la <b>definizione</b> dei termini - parole che vengono utilizzati nel corso elettronico. Il Glossario è uno strumento didattico molto  importante, creato per aiutare soprattutto gli studenti ad adattarsi  meglio al materiale didattico. I termini del Glossario e le loro spiegazioni sono presentanti in un <b>elenco</b>, in ordine alfabetico. Questo modulo permette (se impostato dall' responsabile del corso) la visualizzazione dei termini del glossario  <b>dentro</b> i testi del corso, con una formattazione apposta diversa rispetto alle altre parole, per facilitare la lettura. In questo modo si possono facilmente vedere le spiegazioni dei rispettivi termini, passandoci sopra il puntatore del mouse. Se la spiegazione di un termine contiene un link attivo (hyperlink) a un sito di Internet, cliccandoci ti porta alla pagina corrispondente, la quale potrebbe ad esempio contenere una definizione completa del termine</p> 
</p>";
$langHEBook = "E-Book";
$langHEBook_student = $langHEBook;
$langEBookContent = "<p>L' e-book è un insieme di <b>hypertext</b> (ipertesto) che 'simula' il libro stampato. Sostanzialmente un e-book è una struttura flessibile che, oltre al testo in formato digitale, può essere ulteriormente migliorato con elementi multimediali come: foto, video, link esterni, ecc. In più, questo modulo permette l' impostazione dei contenuti in sezioni-sottosezioni. La presentazione dei contenuti avviene mediante una di lista di scelta (casella di riepilogo).</p>
<hr>

<h2>&nbsp;&nbsp;Preparazione (Passo 1)</h2>
<p>Per creare un nuovo 'E-book' dovresti effettuare un processo di preparazione che comprende la <b>creazione</b> dei file  'html' rispettivi che costituiranno il suo contenuto. È importante rispettare alcune regole semplici (specificazioni) che influiscono in modo diretto sul suo aspetto e sono le seguenti:
</p>
<ol>
  <li> Ogni file (html) deve contenere una sottosezione del E-book (associata con la navigazione interna nel e-Book).</li>
  <li> L'etichetta (titolo) di ogni file (html) dovrebbe avere il titolo esatto della sezione che contiene (ciò sarà usato dal sistema per creare i link di navigazione adatti nell' e-Book).</li>
  <li> Tutti i file (html) con i file necessari abbinati (ad es. immagini, css), devono essere salvati localmente in un file compresso (zip).</li>

</ol>
<hr>

<h2>&nbsp;&nbsp;Creare E-book (Passo 2)</h2>

<ol>
  <li>Prima dovresti accedere alla piattaforma come gestore del corso elettronico, in cui si creerà l' e-Book.</li>
  <li>Seleziona dalla Home Page del corso il modulo <strong>'E-book'</strong></li>
  <li>Clicca sul link  <strong>'Creare' </strong> nel display visualizzato
  <ul>

      <li>Compila il  <strong>titolo</strong> dell' E-book</li>
      <li>Imposta la posizione (locale al vostro / PC) dei file (html) salvati.</li>
    </ul>
  </li>
    <li>Fai clic su  <strong>'Inviare'</strong>.</li>
    <li>In seguito, <strong>attiva</strong> il modulo E-book (se non è già attivato) tramite gli 'Strumenti di gestione'. <br/>

  </li>
</ol>
<hr>
<h2>&nbsp;&nbsp;Struttura dell' E-book (Passo 3)</h2>

<p>Il passo precedente (Passo 2) visualizza un display con varie opzioni ed un elenco dei file (html) già caricati. Puoi impostare la struttura desiderata di un libro, come segue: 
</p>

<ol>
  <li> Inizia con le <strong>Sezioni</strong> del libro, aggiungendone a volontà. Indica il <strong>numero di serie</strong> e il <strong>nome</strong> di ogni sezione nei campi <strong>No </strong> e <strong>Titolo</strong> rispettivamente. </li>

  <li>Poi, configura le <strong>sottosezioni</strong> del libro. Perciò devi:
    <ul>
      <li><strong>associare</strong> ogni file che hai caricato alle sezioni create poco fa.</li>
      <li><strong>indicare</strong> il numero di serie che la sottosezione attuale ha nella sezione che appartiene. <br/>

      <em><u>Nota</​​u>: la piattaforma suggerisce automaticamente come  titoli delle sottosezioni, i nomi dei rispettivi file (li puoi cambiare dopo).</em></li>
    </ul>
</li>
  <li> Appena concluso l' associazione dei file a sezioni e sottosezioni, clicca su<strong>'Inviare'</strong>.</li>
  <li> Il sistema ti informa per il successo della modifica dei dati, e l' E-book è ormai disponibile nel formato specificato.</li>
</ol>

<hr>
<h2>&nbsp;&nbsp;Navigazione in un E-book</h2>

<p> Seleziona dal menu principale (a sinistra) il modulo 'E-book' e dall' elenco visualizzato e fai clic sul nome dell' E-book che desideri sfogliare.</p>

<hr>

<h2>&nbsp;&nbsp;Modificare - Eliminare un E-book</h2>
<p> Se desideri modificare un e-book, dal menu principale (a sinistra) devi selezionare il modulo 'E-book' per visualizzare l'elenco dei disponibili E-Book del corso. Ci deve premere sull' icona corrispondente <strong>'Modificare'</strong>.<br/>
<br/>
Per <strong>eliminare</strong> un E-book,  dal menu principale (a sinistra), devi pure selezionare il modulo 'E-book' per visualizzare l'elenco dei disponibili E-Book del corso e fare clic sull' icona corrispondente <strong>'Eliminare'</strong>.</p>
<hr>
<h2>Gestione dei file  (html) </h2>
<p>Se desideri gestire i file (html) che hai caricato su un certo E-book, devi:</p>
<ul>
  <li>dal menu principale (a sinistra), fare clic sul modulo 'E-Book' e dall' elenco visualizzato premere sull' icona corrispondente <strong>'Modificare'</strong>,</li>
  <li>selezionare <strong>'Gestione File'</strong>, per visualizzare una cartella (directory) con tutti i file html che hai caricato sull' E-book,</li>
  <li>gestire questa cartella e i suoi file, allo stesso modo come i documenti del corso.</li>
</ul>
<hr>


<h2>&nbsp;&nbsp;Associazione a Sezioni Tematiche</h2>
<p>Puoi associare una sezione tematica del tuo corso, alla <strong>sezione</strong> corrispondente dell' <strong>E-book</strong>. Perciò, vai alla sezione del corso alla quale vuoi associare l' E-book,  e dalla barra con gli strumenti disponibili, seleziona <strong>'Aggiungere E-book'</strong>. Verrà visualizzata una pagina in cui devi selezionare la sezione dell' E-book desideri associare. In seguito,  premi <strong>'Aggiungere Selezionati'</strong>. </p>
";
$langEBook_studentContent = "<p>L' e-book è un insieme di <b>hypertext</b> (ipertesto) che 'simula' il libro stampato. Sostanzialmente un e-book è una struttura flessibile che, oltre al testo in formato digitale, può essere ulteriormente migliorato con elementi multimediali come: foto, video, link esterni, ecc. In più, questo modulo permette l' impostazione dei contenuti in sezioni-sottosezioni. La presentazione dei contenuti avviene mediante una di lista di scelta (casella di riepilogo). La sfogliata di un e-book si svolge con modo amichevole, siccome ci sono pulsanti di controllo di varie operazioni, ad esempio pagina precedente - successiva.</p>.";
$langFor_studentContent = "<p>Il modulo 'Forum' permette la comunicazione scritta <b>asincrona</b> tra gli studenti e gli insegnanti. Ad un argomento già postato puoi dare la tua risposta, partecipando così al forum del corso. Per farlo, clicca sul forum che desideri. In seguito fai clic sul argomento che ti interessa da un certo gruppo e premi il link 'Rispondere'. Inserisci il testo che vuoi postare alla discussione particolare e poi clicca sul pulsante 'Inviare'.
</p>
<p>Per ogni forum la piattaforma fornisce una serie di informazioni, come: l' argomento, il numero dei post, l' ultimo (in tempo) post. Una funzione utile di questo modulo  è l'avviso via e-mail dei post inviati. Premendo il pulsante (<img src='$themeimg/email.png' width=16 height=16>) puoi attivare gli avvisi via e-mail dei post inviati al forum della categoria attuale. </p>";
$langHMyAgenda = "Il mio calendario";
$langMyAgendaContent = "<p>Il modulo 'Mio Calendario' ha come obbiettivo l' <b>aggiornamento</b> e l'
<b>organizzazione</b> del tuo tempo rispetto ai processi che si svolgono in
questo corso. La determinazione dell' orario che avranno luogo le
presentazione del corso e  gli esami, ed ancora altri e vari
eventi significativi che riguardano al corso sono disponibili con questo modulo. In maniera grafica, vengono visualizzate le date e
gli intervalli di tempo degli eventi. Qui si dovrebbe chiarire che questo
modulo ha un rapporto immediato con il modulo 'Agenda'.</ p>";
$langHPersonalStats = 'Le mie statistiche';
$langPersonalStatsContent = '
<p>Questo modulo <b>presenta</b> una serie di informazioni utili sui corsi. Ci vengono visualizzati in formato grafico, gli accessi per ogni corso, delle tabelle con gli accessi totali (il tempo e la durata della partecipazione) per ogni corso e il tuo accesso alla piattaforma (data e ora di entrata e di uscita).</p>';
$langInfocoursContent = "<p>Il modulo 'Gestione Corsi' ti permette di modificare / elaborare una serie di parametri che sono associati con un certo corso. Le impostazioni di questi parametri si classificano come segue: </p> 
<hr>
<p> <b>Identità del corso</b></p>
<p>Questa categoria riguarda gli elementi dell'identità del corso. Qui puoi <b>digitare</b> il codice e il titolo di un corso, e l' insegnante-insegnanti che gestiranno il corso. Tramite una casella di riepilogo puoi selezionare la Scuola-Faccoltà che appartiene questo corso, ed ancora il tipo del corso  - ad es. corso post-laurea. Questa categoria si conclude compilando alcune parole-chiave  che si usano dagli studenti affinché loro <b>si concentrino</b> al meglio sui concetti rispettivi. </p>
<hr> 
 <p><b>Accesso al corso</b></p>
<p>Questa categoria è associata all' accesso degli utenti al corso. La piattaforma supporta i seguenti modi d' <b>accesso</b> da parte degli utenti:</p>
$langCourseAccessHelp 
<hr> 
<p><b>Lingua</b></p>
<p>Riguarda alla lingua in cui si visualizzano i messaggi del corso.</p> 
<hr> 
<p><b>Operazioni supportate dal modulo 'Gestione Corsi'</b></p> 
<p>Le operazioni che sono supportate dalla piattaforma sul modulo 'Gestione Corsi' sono i seguenti:</p> 
<ul> 
<li><b>Backup del corso:</b> Puoi creare una copia di backup del corso e salvarlo sul tuo computer locale. Se desideri recuperare il suo contenuto bisogna contattare il gestore della piattaforma.</li> 
<li><b>Eliminare un corso:</b> Eliminando un corso, si elimina il suo contenuto e viene cancellata la registrazione dei suoi utenti. (nota che gli utenti non si rimuovono dalla piattaforma).</li> 
<li><b>Riutilizzare un corso:</b> Puoi eliminare selettivamente alcuni dei dati del corso, per prepararlo per il nuovo anno accademico.</li> 
</ul> 
</p> ";
$langHGroupSpace = "Gruppi di utenti";
$langGroupSpaceContent = "<p>Per modificare gli elementi del gruppo di utenti, fai clic su 'Modificare gruppo di utenti'.
        Cliccando su 'Forum' la piattaforma ti reindirizza al modulo dei 'Forum', in cui un Forum è stato creato per ogni gruppo di utenti. Cliccando sul 'Documenti del gruppo' puoi aggiungere o rimuovere i documenti del gruppo, attraverso i propri link. 
        Qui, deve essere addirittura sottolineato che i documenti del gruppo caricati non sono relativi al modulo 'Documenti' della Home Page della piattaforma. 
        Puoi inviare una e-mail ai membri di un gruppo cliccando su 'Inviare e-mail al gruppo'.
         Puoi anche vedere alcune statistiche per un gruppo cliccando su 'Statistiche' </p>.";
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
