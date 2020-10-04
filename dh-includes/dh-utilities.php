<?php

/**
 * CONFIG
 * 
 * Retrieves a value from the configuration file based on the provided key.
 * 
 * @param String $key The configuration key to retrieve the value for.
 * @return String The associated value to the provided key. Silently fails with an empty string.
 */
function config( String $key ) {
	global $config;
	
	if( isset( $config ) ) {
		if( array_key_exists( $key, $config ) ) {
			return $config[ $key ];
		}
	}
	
	return "";
}

/**
 * LANGUAGE ATTRIBUTE
 * 
 * Echos the lang attribute with the language defined in the configuration file as "lang".
 * Commonly used in the HTML tag.
 */
function lang_attr() {
	if( config( 'lang' ) )
		echo 'lang="' . config( 'lang' ) . '"';
}

/**
 * CHARSET
 * 
 * Echos the character set defined in the configuration file as "charset".
 * Commonly used in the HTML meta tag.
 */
function charset() {
	echo config( 'charset' );
}

/**
 * URL
 * 
 * Generates a full URL based on the "url" defined in the configuration file for the provided path.
 * 
 * @peram String $path The path to generate a URL for.
 * @return String The full generated URL.
 */
function url( String $path = "" ) {
	if( substr( $path, 0, 1 ) == "/" )
		$path = substr( $path, 1 );
	
	$url = config( 'url' );
	
	return substr( $url, -1 ) == "/" ? $url . $path : $url . "/" . $path;
}

/**
 * IS SSL
 * 
 * Determins if the current connection is secure using SSL or HTTPS or PORT 443.
 * 
 * @return boolean A TRUE or FALSE indication of secure connection state.
 */
function is_ssl() {
    if ( isset( $_SERVER['HTTPS'] ) ) {
        if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
            return true;
        }
 
        if ( '1' == $_SERVER['HTTPS'] ) {
            return true;
        }
    } elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}

/**
 * SCHEME
 * 
 * Returns the scheme for the current connection.
 * 
 * @param String "https://" or "http://".
 */
function scheme() {
    return is_ssl() ? "https://" : "http://";
}

/**
 * MARKDOWN
 * 
 * Converts a Markdown formatted string into HTML.
 * 
 * @param String $markdown The markdown formatted string to convert.
 * @return String The converted string as HTML.
 */
function markdown( String $markdown ) {
	$lines = explode( "\n", $markdown );
	
	$result = "";
	
	$is_ordered_list = false;
	$is_unordered_list = false;
	$is_code_block = false;
	
	foreach( $lines as $line ) {
    	$line = htmlentities( $line );
    	
    	if( "### " == substr( $line, 0, 4 ) )
        	$line = "<h3>" . substr( $line, 4 ) . "</h3>";
    	if( "## " == substr( $line, 0, 3 ) )
    	    $line = "<h2>" . substr( $line, 3 ) . "</h2>";
    	if( "# " == substr( $line, 0, 2 ) )
    	    $line = "<h1>" . substr( $line, 2 ) . "</h1>";
    	if( "> " == substr( $line, 0, 2 ) )
    	    $line = "<blockquote>" . substr( $line, 2 ) . "</blockquote>";
    	
    	if( "```" == trim( $line ) && ! $is_code_block ) {
        	$line = str_replace( "```", "<pre>", $line );
        	$is_code_block = true;
        }
    	if( "```" == trim( $line ) && $is_code_block ) {
        	$line = str_replace( "```", "</pre>", $line );
        	$is_code_block = false;
        }
    	
    	
    	$line = str_replace( "---", "<hr>", $line );
    	
    	$line = preg_replace( "/\!\[([a-zA-Z0-9 ]+)\]\(([a-zA-Z0-9\_\-\.\:\/]+)\)/", "<img src=\"$2\" alt=\"$1\" title=\"$1\">", $line );
    	$line = preg_replace( "/\[([a-zA-Z0-9 ]+)\]\(([a-zA-Z0-9\_\-\.\:\/]+)\)/", "<a href=\"$2\">$1</a>", $line );
    	
    	if( 0 !== intval( substr( $line, 0, 1 ) ) && ". " == substr( $line, 1, 2 ) ) {
    	    $line = ( ! $is_ordered_list ? "<ol><li>" : "<li>" ) . substr( $line, 3 ) . "</li>";
        	$is_ordered_list = true;
        } else {
        	if( $is_ordered_list ) {
	        	$is_ordered_list = false;
    	    	$line = "</ol>\n" . $line;
            }
        }
    	
    	if( "- " == substr( $line, 0, 2 ) ) {
    	    $line = ( ! $is_unordered_list ? "<ul><li>" : "<li>" ) . substr( $line, 2 ) . "</li>";
        	$is_unordered_list = true;
        } else {
        	if( $is_unordered_list ) {
	        	$is_unordered_list = false;
    	    	$line = "</ul>\n" . $line;
            }
        }
		
    	$words = explode( " ", $line );
		
    	$is_bold = false;
    	$is_italics = false;
    	$is_code = false;
    	$is_strikethrough = false;
		
    	for( $w = 0; $w < count( $words ); $w++ ) {
    	    if( "**" == substr( $words[ $w ], 0, 2 ) && ! $is_bold ) {
    	        $is_bold = true;
    	        $words[ $w ] = "<b>" . substr( $words[ $w ], 2 );
    	    }
			
    	    if( "**" == substr( $words[ $w ], -2 ) && $is_bold ) {
      	      $is_bold = false;
        	    $words[ $w ] = substr( $words[ $w ], 0, -2 ) . "</b>";
        	}
			
        	if( "*" == substr( $words[ $w ], 0, 1 ) && ! $is_italics ) {
        	    $is_italics = true;
        	    $words[ $w ] = "<i>" . substr( $words[ $w ], 1 );
        	}
			
        	if( "*" == substr( $words[ $w ], -1 ) && $is_italics ) {
        	    $is_italics = false;
        	    $words[ $w ] = substr( $words[ $w ], 0, -1 ) . "</i>";
        	}
			
        	if( "`" == substr( $words[ $w ], 0, 1 ) && ! $is_code ) {
         	   $is_code = true;
        	    $words[ $w ] = "<code class=\"w3-camo-black w3-padding-small w3-round\">" . substr( $words[ $w ], 1 );
        	}
			
        	if( "`" == substr( $words[ $w ], -1 ) && $is_code ) {
        	    $is_code = false;
        	    $words[ $w ] = substr( $words[ $w ], 0, -1 ) . "</code>";
        	}
			
        	if( "`" == substr( $words[ $w ], -2, 1 ) && $is_code ) {
        	    $is_code = false;
        	    $words[ $w ] = substr( $words[ $w ], 0, -2 ) . "</code>.";
        	}
        	
        	if( "~~" == substr( $words[ $w ], 0, 2 ) && ! $is_strikethrough ) {
				$is_strikethrough = true;
        	    $words[ $w ] = "<s>&nbsp;" . substr( $words[ $w ], 2 );
        	}
        	
        	if( "~~" == substr( $words[ $w ], -2, 2 ) && $is_strikethrough ) {
        	    $is_strikethrough = false;
        	    $words[ $w ] = substr( $words[ $w ], 0, -2 ) . "&nbsp;</s>";
        	}
			
        	if( "~~" == substr( $words[ $w ], -3, 2 ) && $is_strikethrough ) {
        	    $is_strikethrough = false;
        	    $words[ $w ] = substr( $words[ $w ], 0, -3 ) . "&nbsp;</s>.";
        	}
    	}
		
		$result .= join( " ", $words ) . "\n";
	}
	
	$result = str_replace( "\n\n", "<br><br>", $result );
	return $result;
}

