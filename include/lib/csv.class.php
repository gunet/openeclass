<?php

/* ========================================================================
 * Open eClass 3.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2016  Greek Universities Network - GUnet
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

/**
 * CSV export utility class
 */

class CSV {

	public $filename = null;
	public $sendBOM = false;
	public $sendSep = false;
    public $debug = false;

	private $outputStarted = false;
	private $encoding;
	private $fieldSeparator = "\t";
	private $recordSeparator = "\r\n";
	private $encodedRecordSeparator;
	private $encodedFieldSeparator;

	public function __construct($encoding=null) {
		if ($encoding) {
			$this->setEncoding($encoding);
		} else {
			$this->setEncoding('UTF-16LE');
		}
	}

	public function setEncoding($encoding) {
		$this->encoding = $encoding;
		$this->encodedRecordSeparator = $this->recordSeparator;
		$this->encodedFieldSeparator = $this->fieldSeparator;
		if (strtolower(substr($encoding, 0, 3)) != 'utf') {
            $this->sendBOM = false;
        } else {
            if ($encoding != 'UTF-8') {
                // Reencode separators for UTF-16
                $this->encodedRecordSeparator = iconv('UTF-8', $encoding, $this->recordSeparator);
                $this->encodedFieldSeparator = iconv('UTF-8', $encoding, $this->fieldSeparator);
                $this->sendBOM = true;
            }
		}
        return $this;
	}

    /**
     * Output a single record, setting up headers and BOM/sep=... line first
     */
	public function outputRecord() {
        $args = func_get_args();
		if (!$this->outputStarted) {
			$this->outputHeaders();
            $this->outputStarted = true;
        }
        $record = array();
        array_walk_recursive($args,
            function($item) use (&$record) {
                $record[] = $this->escape($item);
            });
        if ($this->debug) {
            echo '<tr><td>', implode('</td><td>', $record), '</td></tr>';
        } else {
            echo implode($this->encodedFieldSeparator, $record),
                $this->encodedRecordSeparator;
        }
        return $this;
	}

	public function outputHeaders() {
        if ($this->debug) {
            echo '<p>Encoding: ', $this->encoding, ', filename: ', q($this->filename),
                '</p><table border>';
        } else {
            $filenameAttr = '';
            if ($this->filename) {
                if (preg_match('/[^\x20-\x7E]/', $filenameAttr) and
                    strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
                        $filenameAttr = urlencode($this->filename);
                } else {
                    $filenameAttr = $this->filename;
                }
                // Add quotes to filename if it contains spaces
                if (strpos($filenameAttr, ' ') !== false) {
                    $filenameAttr = '"' . $filenameAttr . '"';
                }
                $filenameAttr = '; filename=' . $filenameAttr;
            }
            header("Content-Type: text/csv; charset=" . $this->encoding);
            header("Content-Disposition: attachment" . $filenameAttr);

            if ($this->sendBOM) {
                switch ($this->encoding) {
                    case 'UTF-8':
                        echo chr(0xEF), chr(0xBB), chr(0xBF);
                        break;
                    case 'UTF-16LE':
                        echo chr(0xFF), chr(0xFE);
                        break;
                    case 'UTF-16LE':
                        echo chr(0xFE), chr(0xFF);
                        break;
                }
            }

            if ($this->sendSep) {
                echo iconv('UTF-8', $this->encoding, 'sep=' . $this->fieldSeparator),
                    $this->encodedRecordSeparator;
            }
        }
	}

    public function escape($string, $force=false) {
        $string = preg_replace('/[\r\n]+/', ' ', trim($string));

        if ($this->debug) {
            return q($string);
        }

        if (($this->fieldSeparator == "\t" and
             preg_match("/[;\t]/", $string)) or
            ($this->fieldSeparator != "\t" and
             preg_match("/[ ,!;\"'\\\\]/", $string)) or $force) {
            $string = '"' . str_replace('"', '""', $string) . '"';
        }
        if ($this->encoding != 'UTF-8') {
            if ($this->encoding == 'Windows-1253') {
                $string = utf8_to_cp1253($string);
            } else {
                $string = iconv('UTF-8', $this->encoding, $string);
            }
        }
        return $string;
    }
}

