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
	
	return substr( $config, -1 ) == "/" ? $url . $path : $url . "/" . $path;
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
