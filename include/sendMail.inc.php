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

// Send a mail message - From: address is always the platform administrator
function send_mail($from, $from_address, $to, $to_address, $subject, $body) {
    if ((is_array($to_address) and !count($to_address)) or empty($to_address)) {
        return true;
    }
    
    $message = Swift_Message::newInstance($subject, $body)
        ->setFrom(fromHeader($from, $from_address));
    if (count($to_address) > 1) {
        $message->setBcc($to_address);
    } else {
        $message->setTo(array($to_address => $to));
    }

    return sendMessage($message);
}


// Send a Multipart/Alternative message, with the proper MIME headers
// and charset tag, with a plain text and an HTML part
// From: address is always the platform administrator, and the
// $from_address specified appears in the Reply-To: header
function send_mail_multipart($from, $from_address, $to, $to_address, $subject, $body_plain, $body_html) {
    if ((is_array($to_address) and !count($to_address)) or empty($to_address)) {
        return true;
    }
    
    $emailAnnounce = get_config('email_announce');
    $body_html = add_host_to_urls($body_html);

    $message = Swift_Message::newInstance($subject)
        ->setFrom(fromHeader($from, $from_address));

    if (count($to_address) > 1) {
        if (isset($emailAnnounce) and !empty($emailAnnounce)) {
            $message->setTo(array($emailAnnounce => $to));
        }
        $message->setBcc($to_address);
    } else {
        if (is_array($to_address)) {
            $to_address = $to_address[0];
        }
        $message->setTo(array($to_address => $to));
    }

    addReplyTo($message, $from, $from_address);

    $message->setBody($body_plain, 'text/plain')
        ->addPart("<html>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset='UTF-8'>
  <title>message</title>
  <style type='text/css'>
    /* General Styles */
    body { padding: 0px; margin: 0px; color: #555; background-color: #f7f7f7; font-family: 'Helvetica', sans-serif; font-size: 1em; }
    #container { margin: 20px; padding: 10px; background-color: #fefefe; }
    #mail-header, #mail-body, #mail-footer { padding: 0 15px 15px; }
    hr { margin: 0px; }

    /* Header Styles */
    #mail-header { padding-top: 10px; border-bottom: 1px solid #ddd; color: #666; }
    #header-title { background-color: #f5f5f5; margin-left: -15px; margin-right: -15px; margin-bottom: 12px; padding: 12px 15px; font-weight: bold; }
    #forum-category { list-style: none; padding-left: 0px; }
    #forum-category li { padding-bottom: 1px; }
    #forum-category li span:first-child { width: 150px; }
    #forum-category li span:last-child { padding-left: 10px; }
    #forum-category { margin-bottom: 0px; }

    /* Body Styles */
    #mail-body-inner { padding-left: 30px; padding-right: 30px; }

    /* Footer Styles */
    #mail-footer { padding-bottom: 25px; border-top: 1px solid #ddd; color: #888; position: relative; }
    #mail-footer-left { float: left; width: 8%; width: 80px; }
    #mail-footer-right { float: left; width: 90%; }
    b.notice { color: #555; }
  </style>
</head>
<body>
  <div id='container'>
    $body_html
  </div>
</body></html>", 'text/html');

    return sendMessage($message);
}

// Try to send a message using Swift Mailer, catching exceptions
function sendMessage($message) {
    global $langMailError;
    try {
        return getMailer()->send($message);
    } catch (Exception $e) {
        Session::Messages("$langMailError<p>" . q($e->getMessage()) . '</p>',
            'alert-danger');
        return false;
    }
}

// Determine the correct From: header
function fromHeader($from, $from_address) {
    global $langVia, $siteName, $charset;

    if (empty($from_address) or !get_config('email_from')) {
        $from_address = get_config('email_sender');
        $from = $siteName;
    } else {
        $from = "$from ($langVia: $siteName)";
    }
    return array($from_address => $from);
}


// Add the correct Reply-To: header if needed
function addReplyTo($message, $from, $from_address) {
    global $emailAdministrator;
        
    // Don't include reply-to if it has been provided by caller
    if ($message->getReplyTo()) {
        return;
    }

    if (!get_config('email_from') and $emailAdministrator <> $from_address) {
        $message->setReplyTo(array($from_address => $from));
    }
}

// Get a Swift Mailer instance depending on configuration
function getMailer() {
    static $mailer;

    if (!isset($transport)) {
        $type = get_config('email_transport');
        if ($type == 'smtp') {
            $transport = Swift_SmtpTransport::newInstance(get_config('smtp_server'), get_config('smtp_port'));
            $username = get_config('smtp_username');
            if ($username) {
                $transport->setUsername($username)->setPassword(get_config('smtp_password'));
            }
            $encryption = get_config('smtp_encryption');
            if ($encryption) {
                $transport->setEncryption($encryption);
            }
        } elseif ($type == 'sendmail') {
            $transport = Swift_SendmailTransport::newInstance(get_config('sendmail_command'));
        } else {
            $transport = Swift_MailTransport::newInstance();
        }
        $mailer = Swift_Mailer::newInstance($transport);
    }
    return $mailer;
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
