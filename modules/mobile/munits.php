<?php

$require_mlogin = true;
$require_mcourse = true;
$require_noerrors = true;

require_once 'minit.php';

$q = Database::get()->querySingle('SELECT view_type FROM course WHERE id = ?d', $course_id)->view_type;

if ($q != 'units') { // course is not in units view
    echo RESPONSE_NO_COURSE_UNITS;
    exit();
}

if ($_SESSION['status'] == USER_TEACHER) {
    $course_units = Database::get()->queryArray("SELECT id, title, comments FROM course_units WHERE course_id = ?d ORDER BY `order` ASC", $course_id);
} else {
    $units = Database::get()->queryArray("SELECT id, title, comments FROM course_units WHERE course_id = ?d AND (visible = 1 OR visible = 2) ORDER BY `order` ASC", $course_id);
    $course_units = findUserVisibleUnits($uid, $units);
}

if (isset($course_units) && count($course_units) > 0) {
    list($courseUnitsDom, $courseUnitsDomRoot) = createCourseUnitsDom($course_units);
} else { // no course units
    echo RESPONSE_NO_COURSE_UNITS;
    exit();
}

if (!defined('M_NOTERMINATE')) {
    echo $courseUnitsDom->saveXML();
    exit();
}

/**
 * @brief create xml file
 * @param $course_units
 * @return array
 * @throws DOMException
 */
function createCourseUnitsDom($course_units) {

    $dom = new DomDocument('1.0', 'utf-8');

    if (defined('M_ROOT')) {
        $root0 = $dom->appendChild($dom->createElement(M_ROOT));
        $root = $root0->appendChild($dom->createElement('units'));
        $retroot = $root0;
    } else {
        $root = $dom->appendChild($dom->createElement('units'));
        $retroot = $root;
    }
    foreach ($course_units as $course_unit) {
        $u = $root->appendChild($dom->createElement('unit'));
        $course_unit_description = ellipsize(html2text($course_unit->comments), 80);
        $u->appendChild(new DOMAttr('id', $course_unit->id));
        $u->appendChild(new DOMAttr('name', $course_unit->title));
        $u->appendChild(new DOMAttr('description', $course_unit_description));
    }

    $dom->formatOutput = true;
    return array($dom, $retroot);
}

<units>
  <unit id="101" name="Εισαγωγή" description=""/>
  <unit id="102" name="Game#1 - Knight Rider" description=""/>
  <unit id="103" name="Game#2 - Downloading bar" description=""/>
  <unit id="104" name="Game#3 - Φανάρια κυκλοφορίας" description=""/>
  <unit id="105" name="Game#4 - Φανάρια αγώνων ταχύτητας" description=""/>
  <unit id="106" name="Game#5 - Πέτρα, ψαλίδι, χαρτί (μέσω σειριακής οθόνης)" description=""/>
  <unit id="107" name="Game#6 - Πέτρα, ψαλίδι, χαρτί (με διακόπτες και LCD οθόνη)" description=""/>
  <unit id="108" name="Game#7 - Pacman, TRex να σωθείς!" description=""/>
  <unit id="109" name="Game#8 - Μπάρα parking (με κουμπί ενεργοποίησης)" description=""/>
  <unit id="110" name="Game#9 - Αυτόματη μπάρα parking (με φωτοαντίσταση)" description=""/>
  <unit id="111" name="Bonus Game" description=""/>
  <unit id="112" name="Game#10 - Arduino hacks T-REX" description=""/>
  <unit id="113" name="Game#11 - Ρομποτικό όχημα" description="The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for t..."/>
  <unit id="114" name="Game#11 - Βοηθητικό σύστημα παρκαρίσματος (paktronic)" description=""/>
  <unit id="115" name="Game#12 - Arduino Radar" description=""/>
  <unit id="116" name="Game#13 - Otto robot" description=""/>
  <unit id="117" name="Game#14 - Μετρητής σκορ" description=""/>
  <unit id="118" name="Game#15 - Τηλεχειρισμός με υπέρυθρες" description=""/>
  <unit id="119" name="Game#16 - Robotic arm" description=""/>
  <unit id="120" name="Ardublock libraries" description=""/>
</units>

