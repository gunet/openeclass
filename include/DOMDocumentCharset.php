<?php

class DOMDocumentCharset extends DOMDocument {
	/**
	 * @param string $html The HTML to load as a DOMDocument
	 * @param string $charset Supply character set manually if HTML documnet does not specify it/is incorrect
	 * @return boolean
	 */
	public function loadHTMLCharset( $html, $charset = '' ) {
		if ( $charset ) {
			// if charset specified, use that
			$html = preg_replace( '|<head>|i', 
								  '<head><meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">', 
								  $html );

			// @todo if charset declaration already exists, replace it
		}
		else {
			// libxml version < 2.80 requires this workaround as it doesn't correctly parse HTML5 charset declarations
			if ( LIBXML_VERSION < 20800 ) {
				$html = preg_replace( '/<meta charset="(.+)">/', 
						              '<meta http-equiv="Content-Type" content="text/html; charset=$1">', 
						              $html );
			}
		}

		return $this->loadHTML( $html );
	}

	public function loadHTMLFileCharset( $filename, $charset = '' ) {
		// load HTML doc from filename into a string
		$html = file_get_contents( $filename );

		return $this->loadHTMLCharset( $html, $charset );
	}	
}
