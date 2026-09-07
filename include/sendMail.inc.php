<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

// Send a mail message - From: address is always the platform administrator
function send_mail($from, $from_address, $to, $to_address, $subject, $body) {
    if ((is_array($to_address) and !count($to_address)) or empty($to_address)) {
        return true;
    }

    $email = (new Email())
        ->subject($subject)
        ->text($body);

    $fromHeader = fromHeader($from, $from_address);
    if ($fromHeader) {
        $email->from($fromHeader);
    }

    if (is_array($to_address) and count($to_address) > 1) {
        foreach ($to_address as $address) {
            $email->addBcc($address);
        }
    } else {
        if (is_array($to_address)) {
            $to_address = $to_address[0];
        }
        $email->to(new Address($to_address, $to));
    }

    return sendMessage($email);
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

    $email = (new Email())
        ->subject($subject);

    $fromHeader = fromHeader($from, $from_address);
    if ($fromHeader) {
        $email->from($fromHeader);
    }

    if (is_array($to_address)) {
        if (count($to_address) > 1) {
            if (isset($emailAnnounce) and !empty($emailAnnounce)) {
                $email->to(new Address($emailAnnounce, $to));
            }
            foreach ($to_address as $address) {
                $email->addBcc($address);
            }
        } else {
            $to_address = $to_address[0];
            $email->to(new Address($to_address, $to));
        }
    } else {
        $email->to(new Address($to_address, $to));
    }

    addReplyTo($email, $from, $from_address);

    $email->text($body_plain)
        ->html("<html>
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
</body></html>");

    return sendMessage($email);
}

// Try to send a message using Symfony Mailer, catching exceptions
function sendMessage($email) {
    global $langMailError;

    $email_bounces = get_config('email_bounces');
    if ($email_bounces) {
        $email->returnPath($email_bounces);
    }

    try {
        getMailer()->send($email);
        return true;
    } catch (TransportExceptionInterface $e) {
        Session::flash('message', "$langMailError<p>" . q($e->getMessage()) . '</p>');
        Session::flash('alert-class', 'alert-danger');
        return false;
    } catch (Exception $e) {
        Session::flash('message', "$langMailError<p>" . q($e->getMessage()) . '</p>');
        Session::flash('alert-class', 'alert-danger');
        return false;
    }
}

// Determine the correct From: header
function fromHeader($from, $from_address) {
    global $langVia, $siteName, $langInvalidEmailRecipient;

    if (empty($from_address) or !get_config('email_from')) {
        $from_address = get_config('email_sender');
        $from = $siteName;
    } else {
        $from = "$from ($langVia: $siteName)";
    }

    if (!valid_email($from_address)) { // check if sender address is valid
        Session::flash('message', "$langInvalidEmailRecipient");
        Session::flash('alert-class', 'alert-danger');
        return null;
    } else {
        return new Address($from_address, $from);
    }
}


// Add the correct Reply-To: header if needed
function addReplyTo($email, $from, $from_address) {
    global $emailAdministrator, $langInvalidEmailRecipient;

    // Don't include reply-to if it has been provided by caller
    if ($email->getReplyTo()) {
        return;
    }

    if (!get_config('email_from') and $emailAdministrator <> $from_address) {
        if (!valid_email($from_address)) { // check if sender address is valid
            Session::flash('message', "$langInvalidEmailRecipient");
            Session::flash('alert-class', 'alert-danger');
            return;
        } else {
            $email->addReplyTo(new Address($from_address, $from));
        }
    }
}

// Get a Symfony Mailer instance depending on configuration
function getMailer() {
    static $mailer;

    if (!isset($mailer)) {
        $type = get_config('email_transport');
        if ($type == 'smtp') {
            $host = get_config('smtp_server');
            $port = get_config('smtp_port');
            $user = get_config('smtp_username');
            $pass = get_config('smtp_password');
            $encryption = get_config('smtp_encryption');

            $userPass = '';
            if ($user) {
                $userPass = urlencode($user) . ':' . urlencode($pass) . '@';
            }

            $dsn = ($encryption == 'ssl' ? 'smtps' : 'smtp') . '://' . $userPass . $host . ($port ? ':' . $port : '');
            $transport = Transport::fromDsn($dsn);
        } elseif ($type == 'sendmail') {
            $command = get_config('sendmail_command');
            if ($command) {
                $transport = new Symfony\Component\Mailer\Transport\SendmailTransport($command);
            } else {
                $transport = new Symfony\Component\Mailer\Transport\SendmailTransport();
            }
        } else {
            $transport = new Symfony\Component\Mailer\Transport\SendmailTransport();
        }
        $mailer = new Mailer($transport);
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
