***********************************
Μαθηματικές φόρμουλες στο eClass
***********************************
Από την έκδοση GUnet eClass 1.4 ενσωματώθηκε στην πλατφόρμα
το σύστημα Latex. Xρησιμοποιώντας το latex, στα υποσυστήματα 
"Ανακοινώσεις", "Περιοχή συζητήσεων" και "Ασκήσεις" καθίσταται 
δυνατόν να γράψουμε μαθηματικές φόρμουλες στα ηλεκτρονικά 
μαθήματα που φιλοξενούνται στην πλατφόρμα.
Το σύστημα αυτό εγκαταστάθηκε και δοκιμάστηκε με επιτυχία σε 
διάφορες διανομές του Linux (Redhat, Debian κ.λπ.) 
χρησιμοποιώντας τον κώδικα του Latexrender. To Latexrender είναι 
ένα πρόγραμμα που παράγει εικόνες gif μαθηματικών εκφράσεων.
Ο κώδικας του latexrender έχει άδεια GNU Lesser General Public License.

*************************
Απαιτήσεις του Server
*************************
Τα Latex, ImageMagick και Ghostscript, πρέπει να εγκατασταθούν 
στο σύστημα που φιλοξενεί την πλατφόρμα GUnet eClass. Πολλές φορές 
το tetex χρησιμοποιείται ως το latex σύστημα. Καλό θα είναι να 
εγκατασταθεί το συνολικό package.

************
Ρυθμίσεις 
************

A) Ανοίχτε το αρχείο ρυθμίσεων του eClass config.php.
(π.χ. /var/www/html/eclass/config/config.php)

Προσθέστε τις παρακάτω γραμμές

define('latex_picture_path', "/path-to-eclass/modules/latexrender/pictures");
define('latex_picture_path_httpd',"/path-to-eclass/modules/latexrender/pictures");
define('latex_tmp_dir',"/path-to-eclass/modules/latexrender/tmp"); 
define('latex_path',"/usr/bin/latex");
define('dvips_path',"/usr/bin/dvips");
define('convert_path',"/usr/bin/convert");
define('identify_path',"usr/bin/identify");

Προφανώς όπου path-to-eclass θα πρέπει να βάλετε το path της εγκατάστασης.
Στο παράδειγμά μας θα πρέπει να είναι:

define('latex_picture_path', "/var/www/html/eclass/modules/latexrender/pictures");
define('latex_picture_path_httpd',"/path-to-eclass/modules/latexrender/pictures");
define('latex_tmp_dir',"/path-to-eclass/modules/latexrender/tmp"); // χωρίς τελικό slash

Επίσης ελέγξτε τα path των προγραμμάτων latex, dvips, 
convert και identify και αλλάξτε τα κατάλληλα, αν απαιτείται.

B) Μεταβείτε στον κατάλογο (κατάλογο εγκατάστασης του eClass)/modules/latexrender.
(π.χ. /var/www/html/eclass/modules/latexrender )

Εκεί θα βρείτε δύο υποκαταλόγους με τα ονόματα pictures και tmp. 
Αυτοί οι κατάλογοι πρέπει να έχουν πλήρη δικαιώματα (chmod 777) ώστε το πρόγραμμα να μπορεί να 
αποθηκεύει αρχεία σε αυτούς.

Μπορεί να υπάρχει πρόβλημα με τη διαφάνεια των εικόνων καθώς 
το ImageMagick αγνοεί την εντολή για τη διαφάνεια. Αν δεν 
θέλουμε το background να είναι άσπρο τότε στο αρχείο 
class.latexrender.php αλλάζουμε το "png" σε "gif" και οι 
εικόνες θα είναι διαφανείς.

Η φόρμουλα γράφεται ανάμεσα στα δύο texbox [tex] και [/tex]. 
Έτσι [tex]\sqrt{2}[/tex] θα παράγει ένα διαφανές gif που 
εμφανίζει την τετραγωνική ρίζα του 2. 

Προαιρετικές αλλαγές: 

Αν η φόρμουλα έχει μεγάλο μέγεθος (12pt) μπορούμε να το 
ελαττώσουμε αλλάζοντας στο αρχείο class.latexrender.php τον 
κώδικα: 
var $_font_size = 12;
στον κώδικα:
var $_font_size = 10;

Μόνο τα μεγέθη 10pt, 11pt και 12pt υποστηρίζονται.

Στο αρχείο latex.php έχει προστεθεί η γραμμή του κώδικα: 

$latex_formula = "\n\n" .$latex_formula; 

Που σημαίνει ότι μπορούμε να γράψουμε εξισώσεις που αρχίζουν με \begin.
Αυτό όμως προκαλεί πρόβλημα στη χρήση του \frac στην 
αρχή μιας εξίσωσης. Αντί αυτού μπορούμε να:
1. Σβήσουμε τη γραμμή $latex_formula = "\n\n" .$latex_formula; 
και να προσθέσουμε δύο κενές γραμμές όταν χρησιμοποιούμε το \begin 
στην αρχή της εξίσωσης.
2. Χρησιμοποιήσουμε το \dfrac αντί του \frac το οποίο δίνει 
το σωστό αποτέλεσμα.

Δοκιμές:
[tex]\sqrt{2}[/tex]
[tex]\dfrac {1}{2}[/tex]
[tex]\dfrac{\sin x}{x}=1[/tex]
</div>
