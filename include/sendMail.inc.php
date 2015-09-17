<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

// Send a mail message, with the proper MIME headers and charset tag
// From: address is always the platform administrator, and the
// $from_address specified appears in the Reply-To: header
function send_mail($from, $from_address, $to, $to_address, $subject, $body, $charset, $extra_headers = '') {
    if ((is_array($to_address) and !count($to_address)) or empty($to_address)) {
        return true;
    }
    if (count($to_address) > 1) {
        $to_header = '(undisclosed-recipients)';
        $bcc = 'Bcc: ' . join(', ', $to_address) . PHP_EOL;
    } else {
        if (empty($to)) {
            $to_header = $to_address;
        } else {
            $to_header = qencode($to, $charset) . " <$to_address>";
        }
        $bcc = '';
    }
    $headers = from($from, $from_address) . $bcc .
		"MIME-Version: 1.0" . PHP_EOL .
		"Content-Type: text/plain; charset=$charset" . PHP_EOL .
        "Content-Transfer-Encoding: 8bit" .
        reply_to($from, $from_address, $extra_headers);
    if ($extra_headers) {
		$headers .= PHP_EOL . preg_replace('/\n+/', PHP_EOL, $extra_headers);
    }

    return @mail($to_header, qencode($subject, $charset), $body, $headers);
}


// Send a Multipart/Alternative message, with the proper MIME headers
// and charset tag, with a plain text and an HTML part
// From: address is always the platform administrator, and the
// $from_address specified appears in the Reply-To: header
function send_mail_multipart($from, $from_address, $to, $to_address, $subject, $body_plain, $body_html, $charset) {

    if ((is_array($to_address) and !count($to_address)) or empty($to_address)) {
        return true;
    }
    
    $emailAnnounce = get_config('email_announce');
    $body_html = add_host_to_urls($body_html);

    if (count($to_address) > 1) {
        if (isset($emailAnnounce) and (!(empty($emailAnnounce)))) {
            if (empty($to)) {
                $to_header = $emailAnnounce;
            } else {
                $to_header = $to . " <$emailAnnounce>";
            }
        } else {
            if (empty($to)) {
                $to_header = '(undisclosed recipients)';
            } else {
                $to_header = "($to)";
            }
        }
                $bcc = 'Bcc: ' . join(', ', $to_address) . PHP_EOL;
    } else {
        if (empty($to)) {
            if (is_array($to_address)) {
                $to_header = $to_address[0];
            } else {
                $to_header = $to_address;
            }
        } else {
            if (is_array($to_address)) {
                $to_header = qencode($to, $charset) . " <{$to_address[0]}>";
            } else {
                $to_header = qencode($to, $charset) . " <$to_address>";
            }
        }
        $bcc = '';
    }
    $separator = uniqid('==eClass-Multipart_Boundary_0_', true) . '_' .
            md5(time());
    $headers = from($from, $from_address) . $bcc .
		   "MIME-Version: 1.0" . PHP_EOL .
           "Content-Type: multipart/alternative;" . PHP_EOL .
           "    boundary=\"$separator\"" .
            reply_to($from, $from_address);

	$body = "This is a multi-part message in MIME format." . PHP_EOL . PHP_EOL .
		"--$separator" . PHP_EOL .
		"Content-Type: text/plain; charset=$charset" . PHP_EOL .
		"Content-Transfer-Encoding: 8bit\n\n$body_plain" . PHP_EOL . PHP_EOL .
		"--$separator" . PHP_EOL .
		"Content-Type: text/html; charset=$charset" . PHP_EOL .
		"Content-Transfer-Encoding: 8bit" . PHP_EOL . PHP_EOL .
        "<html><head><meta http-equiv='Content-Type' " .
        "content='text/html; charset=\"$charset\"'>" .
        "<title>message</title>".
        "<style type='text/css'>
            /* General Styles */
            body{ padding: 0px; margin: 0px; color: #555; background-color: #f5f5f5; font-family: 'Helvetica', sans-serif; font-size: 1em; }
            #container{ margin: 20px; padding: 10px; background-color: #fefefe; }
            #mail-header, #mail-body, #mail-footer{ padding:  0 15px 15px; }
            hr{ margin: 0px; }

            /* Header Styles */
            #mail-header{ padding-top: 10px; border-bottom: 1px solid #ddd; color: #666; }
            #header-title{ background-color: #f5f5f5; margin-left: -15px; margin-right: -15px; padding: 12px 15px; }
            #forum-category{ list-style: none; padding-left: 0px; }
            #forum-category li{ padding-bottom: 1px; }
            #forum-category li span:first-child{ width: 150px; }
            #forum-category li span:last-child{ padding-left: 10px; }
            #forum-category{ margin-bottom: 0px; }

            /* Body Styles */
            #mail-body-inner{ padding-left: 30px; padding-right: 30px; }

            /* Footer Styles */
            #mail-footer{ padding-bottom: 25px; border-top: 1px solid #ddd; color: #888; position: relative; }
            #mail-footer-left{ float: left; width: 8%; width: 80px; }
            #mail-footer-right{ float: left; width: 90%; }
            b.notice{ color: #555; }
        </style>\n".
        "</head><body><div id='container'>\n" .
		"$body_html\n</div></body></html>" . PHP_EOL .
		"--$separator--" . PHP_EOL;
	return @mail($to_header, qencode($subject, $charset),
               $body, $headers);
}


// Determine the correct From: header
function from($from, $from_address) {
    global $langVia, $siteName, $charset;

    if (empty($from_address) or !get_config('email_from')) {
        $from_address = get_config('email_sender');
        return "From: " . qencode($siteName, $charset) .
                " <$from_address>\n";
    } else {
        return "From: " .
                qencode("$from ($langVia: $siteName)", $charset) .
                " <$from_address>" . PHP_EOL;
    }
}


// Determine the correct Reply-To: header if needed
function reply_to($from, $from_address, $extra_headers='') {
    global $emailAdministrator, $charset;
        
    // Don't include reply-to if it has been provided by caller
    if (strpos(strtolower($extra_headers), 'reply-to:') !== false) {
        return '';
    }

    if (!get_config('email_from') and $emailAdministrator <> $from_address) {
        if (empty($from)) {
            return PHP_EOL . "Reply-To: $from_address";
        } else {
            return PHP_EOL . "Reply-To: " .
                   qencode($from, $charset) .
                   " <$from_address>";
        }
    } else {
        return '';
    }
}


// Encode a mail header line with according to MIME / RFC 2047
function qencode($header, $charset) {
    // If header contains no chars > 128, return it without encoding
    if (!preg_match('/[\200-\377]/', $header)) {
        return $header;
    } else {
        mb_internal_encoding('UTF-8');
        return mb_encode_mimeheader($header, $charset);
    }
}


/**
 * Make sure URLs appearing in href and src attributes in HTML include a host. 
 * 
 * @param string $html  - The HTML snippet to canonicalize
 * @return string       - The canonicalized HTML
 */
function add_host_to_urls($html) {
    global $urlServer, $urlAppend;
    static $html_memo, $out_memo;

    if (!isset($html_memo) or $html_memo != $html) {
        $html_memo = $html;
        $url_start = substr($urlServer, 0, strlen($urlServer) - strlen($urlAppend));
        $dom = new DOMDocument();
        @$dom->loadHTML('<div>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8') . '</div>');

        foreach (array('a' => 'href', 'img' => 'src') as $tag_name => $attribute) {
            $elements = $dom->getElementsByTagName($tag_name);
            if ($elements instanceof DOMNodeList) {
                foreach ($elements as $element) {
                    $url = $element->getAttribute($attribute);
                    if ($url) {
                        $url_info = parse_url($url);
                        if (!isset($url_info['scheme']) and !isset($url_info['host'])) {
                            $element->setAttribute($attribute, $url_start . $url);
                        }
                    }
                }
            }
        }

        $base_node = $dom->getElementsByTagName('div')->item(0);
        $out_memo = dom_save_html($dom, $base_node);
    }
    return $out_memo;
}
