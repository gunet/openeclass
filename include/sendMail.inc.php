<?

/*
 +----------------------------------------------------------------------+
 | e-class version 1.3                                                  |
 | based on CLAROLINE version 1.3.0                                     |
 +----------------------------------------------------------------------+
 | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
 | Copyright (c) 2003, 2004 GUNet                                       |
 +----------------------------------------------------------------------+
 | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
 |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
 |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
 |                                                                      |
 | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
 |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
 |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
 +----------------------------------------------------------------------+
 | MIME and e-mail functions                                            |
 +----------------------------------------------------------------------+
*/

// Send a mail message, with the proper MIME headers and charset tag
function send_mail($from, $from_address, $to, $to_address,
                   $subject, $body, $charset)
{
	if (empty($to)) {
		$to_header = $to_address;
	} else {
		$to_header = qencode($to, $charset) . " <$to_address>";
	}
	$headers =
		"From: " . qencode($from, $charset) . " <$from_address>\n" .
		"MIME-Version: 1.0\n" .
		"Content-Type: text/plain; charset=$charset\n" .
		"Content-Transfer-Encoding: 8bit";

	return @mail($to_header, qencode($subject, $charset),
               $body, $headers);
}


// Send a Multipart/Alternative message, with the proper MIME headers
// and charset tag, with a plain text and an HTML part
function send_mail_multipart($from, $from_address, $to, $to_address,
                   $subject, $body_plain, $body_html, $charset)
{
	if (empty($to)) {
		$to_header = $to_address;
	} else {
		$to_header = qencode($to, $charset) . " <$to_address>";
	}
	$separator = '----=_NextPart_000_0000_01C-eclass-5F02B.B43B1CC0';
	$headers =
		"From: " . qencode($from, $charset) . " <$from_address>\n" .
		"MIME-Version: 1.0\n" .
		"Content-Type: multipart/alternative;" .
		"\n\tboundary=\"$separator\"\n";

	$body = "This is a multi-part message in MIME format.\n\n" .
		"--$separator\n" .
		"Content-Type: text/plain; charset=$charset\n" .
		"Content-Transfer-Encoding: 8bit\n\n$body_plain\n\n" .
		"--$separator\n" .
		"Content-Type: text/html; charset=$charset\n" .
		"Content-Transfer-Encoding: 8bit\n\n" .
		"<html><head><meta http-equiv='Content-Type' " .
		"content='text/html; charset=\"$charset\"'>" .
		"<title>message</title></head><body>\n" .
		"$body_html\n</body></html>\n\n" .
		"--$separator--\n";

	return @mail($to_header, qencode($subject, $charset),
               $body, $headers);
}


// Encode a mail header line with q-encoding according to MIME / RFC 2047
function qencode($header, $charset)
{
	// If header contains no chars > 128, return it without encoding
	if (!preg_match('/[\200-\377]/', $header)) {
		return $header;
	}

	// Maximum length of encoded lines allowed, minus the charset
	// declaration and delimiters overhead
	$maxlen = 75 - strlen($charset) - 9;

	// Charset declaration
	$decl = "=?$charset?q?";

	// Start with an empty line, set current length > maxlen so that
	// a charset declaration is put at the beginning
	$quoted = '';
	$current = $maxlen + 1;
	$beginning = TRUE;

	for ($i = 0; $i < strlen($header); $i++) {
		if ($current >= $maxlen) {
			if ($beginning) {
				$quoted .= "$decl";
				$beginning = FALSE;
			} else {
				$quoted .= "?=\n   $decl";
			}
			$current = strlen($decl);
		}
		$c = $header{$i};
		if (ord($c) < 32) {
			// Ignore control characters
		} elseif ($c == ' ') {
			// Space turns to '_'
			$quoted .= '_';
			$current++;
		} elseif (preg_match('/^[a-zA-Z0-9\,\*\+\-\!\/]$/', $c)) {
			// These don't need quoting
			$quoted .= $c;
			$current++;
		} else {
			// All others get quoted
			$quoted .= sprintf("=%02x", ord($c));
			$current += 3;
		}
	}
	$quoted .= "?=";

	return $quoted;
}

?>
