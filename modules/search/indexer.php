<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

class Indexer {

    private static $lookup = array(
        // Greek doubles
        'αι' => 'e', 'Αι' => 'E', 'ΑΙ' => 'E', 'αί' => 'e', 'Αί' => 'E', 'ει' => 'i', 'Ει' => 'i', 'ΕΙ' => 'i', 'εί' => 'i', 'Εί' => 'i',
        'ου' => 'u', 'Ου' => 'U', 'ΟΥ' => 'U', 'ού' => 'u', 'Ού' => 'U', 'οι' => 'i', 'Οι' => 'i', 'ΟΙ' => 'i', 'Οί' => 'i', 'Οί' => 'i',
        'υι' => 'i', 'Υι' => 'i', 'ΥΙ' => 'i', 'υί' => 'i', 'Υί' => 'i',
        'ββ' => 'b', 'ΒΒ' => 'B', 'γγ' => 'gk', 'ΓΓ' => 'gk', 'κκ' => 'k', 'ΚΚ' => 'K', 'λλ' => 'l', 'ΛΛ' => 'L',
        'μμ' => 'm', 'MM' => 'M', 'νν' => 'n', 'ΝΝ' => 'N', 'ππ' => 'p', 'ΠΠ' => 'P', 'ρρ' => 'r', 'ΡΡ' => 'r',
        'σσ' => 's', 'ΣΣ' => 'S', 'ττ' => 't', 'ΤΤ' => 'T',
        // Greek letters
        'α' => 'a', 'ά' => 'a', 'Α' => 'A', 'Ά' => 'A', 'β' => 'b', 'Β' => 'B', 'γ' => 'g', 'Γ' => 'G', 'δ' => 'd', 'Δ' => 'D',
        'ε' => 'e', 'έ' => 'e', 'Ε' => 'E', 'Έ' => 'E', 'ζ' => 'z', 'Ζ' => 'Z', 'η' => 'i', 'ή' => 'i', 'Η' => 'I', 'Ή' => 'I',
        'θ' => 'q', 'Θ' => 'Q', 'ι' => 'i', 'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I',
        'κ' => 'k', 'Κ' => 'K', 'λ' => 'l', 'Λ' => 'L', 'μ' => 'm', 'Μ' => 'M', 'ν' => 'n', 'Ν' => 'N', 'ξ' => 'j', 'Ξ' => 'J',
        'ο' => 'o', 'ό' => 'o', 'Ο' => 'O', 'Ό' => 'O', 'π' => 'p', 'Π' => 'P', 'ρ' => 'r', 'Ρ' => 'R', 'σ' => 's', 'ς' => 's', 'Σ' => 'S',
        'τ' => 't', 'Τ' => 'T', 'υ' => 'i', 'ύ' => 'i', 'ϋ' => 'i', 'ΰ' => 'i', 'Υ' => 'I', 'Ύ' => 'I', 'Ϋ' => 'I',
        'φ' => 'f', 'Φ' => 'F', 'χ' => 'x', 'Χ' => 'X', 'ψ' => 'c', 'Ψ' => 'C', 'ω' => 'o', 'ώ' => 'o', 'Ω' => 'O', 'Ώ' => 'O',
        // Latin
        'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
        'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U',
        'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
        'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
        'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f'
    );

    public function storeToIndex($text) {
        // ...
    }

    public function search($text) {
        // ...
    }

    public function test() {
        $phtext = "αβγδεζηθικλμνξοπρσςτυφχψω άέίύήόώ ϊΐϋΰ ΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩ ΆΈΊΎΉΌΏ ΪΫ Αι έννοιαι των Αιρέσεων του Αββαείου";
        if (self::phonetics($phtext, 0) != "abgdeziqiklmnjoprsstifxco aeiiioo iiii ABGDEZIQIKLMNJOPRSTIFXCO AEIIIOO II E enie ton Ereseon tu Abaiu")
            return 0;

        return 1;
    }

    private static function phonetics($text, $ignoreCase = 1) {
        $result = strtr($text, self::$lookup);
        if ($ignoreCase) {
            $result = strtolower($result);
        }
        return $result;
    }

}

$idx = new Indexer();

echo $idx->test();
?>
