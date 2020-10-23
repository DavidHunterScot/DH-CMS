<?php

$themes_dir = __DIR__ . DS . '..' . DS . 'dh-content' . DS . 'themes';
$available_themes = array();

$theme_info = array();

$theme_styles = array();
$theme_scripts = array();

$theme_supports = array();

$theme_body_classes = array();

$error_404 = false;

$request_path = "";

/**
 * GET AVAILABLE THEMES
 * 
 * Returns all valid themes based on files found within the dh-content themes directory.
 * 
 * @return array An array of theme directory names that are valid and available for use.
 */
function get_available_themes() {
	global $themes_dir, $available_themes;
	
	if( count( $available_themes ) > 0 )
		return $available_themes;
	
	if( ! file_exists( $themes_dir ) )
		return array();
	
	$themes_dir_contents = scandir( $themes_dir );
	
	for( $t = 0; $t < count( $themes_dir_contents ); $t++ ) {
		$theme_dir_path = $themes_dir . DS . $themes_dir_contents[ $t ];
		
		if( $themes_dir_contents[ $t ] != "." && $themes_dir_contents[ $t ] != ".." && is_dir( $theme_dir_path ) && file_exists( $theme_dir_path . DS . 'theme.css' ) && file_exists( $theme_dir_path . DS . 'index.php' ) ) {
			$available_themes[] = $themes_dir_contents[ $t ];
		}
	}
	
	return $available_themes;
}

/**
 * THEME EXISTS
 * 
 * Checks if the provided theme directory name exists as a valid theme based on
 * get_available_themes().
 * 
 * @param String $theme The theme directory name to check.
 * @return boolean TRUE or FALSE indication of existence.
 */
function theme_exists( String $theme ) {
	$themes = get_available_themes();
	
	if( is_array( $themes ) && count( $themes ) > 0 ) {
		if( in_array( $theme, $themes ) ) {
			return true;
		}
	}
	
	return false;
}

/**
 * THEME INFO (ORIGINAL)
 * 
 * Fetches info about a theme based on the provided key and matching it against
 * the comment at the top of theme.css file of theme.
 * 
 * @param String $key The key as a lowercase hyphen-separated string.
 * @param String $theme (Optional) The directory name for the root of the theme. Defaults to current theme.
 * @return String The value associated with the key if found. Silently fails with empty string.
 */
function theme_info( String $key, String $theme = "" ) {
	if( "" == $theme )
    	$theme = config( 'theme' );
    
    $key = str_replace( "-", " ", $key );
    $key = ucwords( $key );
	
	global $themes_dir;
	
	if( theme_exists( $theme ) ) {
		$theme_css = file_get_contents( $themes_dir . DS . $theme . DS . 'theme.css' );
		
		$theme_css_lines = explode( "\n", $theme_css );
		
		$is_info_comment = false;
		
		for( $line = 0; $line < count( $theme_css_lines ); $line++ ) {
			$line_content = trim( $theme_css_lines[ $line ] );
			
			if( 0 == $line && "/*" == $line_content || "/**" == $line_content ) {
			    $is_info_comment = true;
			    continue;
			}
			
			if( true == $is_info_comment && "*/" == $line_content || "**/" == $line_content ) {
			    $is_info_comment = false;
			    continue;
			}
			
			if( $is_info_comment && strpos( $line_content, ': ' ) > 0 ) {
				$info_key = substr( $line_content, 0, strpos( $line_content, ': ' ) );
				$info_value = substr( $line_content, strpos( $line_content, ': ' ) + 2 );
				
				if( $info_key == $key )
					return $info_value;
			}
		}
	}
	
	return "";
}

/**
 * THEME INFO
 * 
 * Fetches info about a theme based on the provided key and matching it against
 * the comment at the top of theme.css file of theme.
 * 
 * Function will read each line at a time and will stop reading when reached end of comment,
 * or if first line is not the start of a comment.
 * 
 * Theme info will be cached to an array upon first call, then pull from array on future calls.
 * 
 * @param String $key The key as a lowercase hyphen-separated string.
 * @param String $theme (Optional) The directory name for the root of the theme. Defaults to current theme.
 * @return String The value associated with the key if found. Silently fails with empty string.
 */
function theme( String $key, String $theme = "" ) {
	if( "" == $theme )
    	$theme = config( 'theme' );
    
    $key = str_replace( "-", " ", $key );
    $key = ucwords( $key );

    global $theme_info;
    if( array_key_exists( $theme, $theme_info ) ) {
    	if( is_array( $theme_info[ $theme ] ) && array_key_exists( $key, $theme_info[ $theme ] ) && $theme_info[ $theme ][ $key ] ) {
    		return $theme_info[ $theme ][ $key ];
    	}
    	return "";
    }
	
	global $themes_dir;

	$info_loaded = false;
	
	if( theme_exists( $theme ) ) {
		try {
			$file = new SplFileObject( $themes_dir . DS . $theme . DS . 'theme.css' );
			$line_number = 1;

			$is_info_comment = false;
			$read_file = true;

			while ( ! $file->eof() && $read_file ) {
				$line = trim( $file->fgets() );
				$info_loaded = true;

				if( 1 == $line_number && ( "/*" == $line || "/**" == $line ) ) {
					$is_info_comment = true;
					continue;
				}

				if( $is_info_comment && ( "*/" == $line || "**/" == $line ) ) {
					$is_info_comment = false;
					$read_file = false;
					continue;
				}

				if( $is_info_comment && strpos( $line, ': ' ) > 0 ) {
					$info_key = substr( $line, 0, strpos( $line, ': ' ) );
					$info_value = substr( $line, strpos( $line, ': ' ) + 2 );
					
					$theme_info[ $theme ][ $info_key ] = $info_value;
				}

				$line_number++;
			}

			if( $info_loaded )
				return theme( $key, $theme );
		} catch( Exception $ex ) {
			error_log( "dh-themes.php theme() error: unable to read from theme.css file." );
		}
	}
	
	return "";
}

/**
 * LOAD THEME PART
 * 
 * Require Once the part of the current theme based on a PHP file with the same name without the extension.
 * 
 * @param String $part The PHP filename without the extension to require in from within the theme directory.
 */
function theme_load_part( String $part ) {
	global $themes_dir;
	
	$path_to_part_file = theme_file_path( $part );
		
	if( file_exists( $path_to_part_file ) ) {
		require_once $path_to_part_file;
	}
}

/**
 * THEME FILE PATH
 * 
 * Find the absolute path to any file within the theme root directory.
 * 
 * @param String $part The file name without file extension.
 * @param String $extension (Optional) The file extension without the dot related to the $part, defaulting to "php".
 * @return String The absolute path to the requested file. Silently fails to empty string.
 */
function theme_file_path( String $part, String $extension = "php" ) {
    global $themes_dir;
    
    $path = $themes_dir . DS . config('theme') . DS . $part . "." . $extension;
    
    if( file_exists( $path ) )
        return $path;
    return "";
}

/**
 * THEME REGISTER STYLE
 * 
 * Register a stylesheet for loading later. This will ensure only one of each stylesheet is loaded.
 * Content-Type of file at path must be text/css.
 * 
 * TODO: Add dependency support.
 * 
 * @param String $id The unique ID to register the style with, which also appears within the id attribute of the HTML link tag as "$id-css".
 * @param String $path The URL path to the stylesheet file to load.
 * @param String $ver (Optional) A version number to append to the URL as a query string ?ver=12345.
 */
function theme_register_style( String $id, String $path, String $ver = "" ) {
	global $theme_styles;
	
	if( is_array( $theme_styles ) && ! array_key_exists( $id, $theme_styles ) ) {
	    if( gethostbyaddr( parse_url( $path, PHP_URL_HOST ) ) ) {
			$headers = get_headers( $path, 1 );
			
			if( array_key_exists( 'Content-Type', $headers ) && 'text/css' == $headers['Content-Type'] ) {
				$theme_styles[ $id ][ 'id' ] = $id;
				$theme_styles[ $id ][ 'path' ] = $path;
				
				if( $ver != "" )
					$theme_styles[ $id ][ 'ver' ] = $ver;
			}
		}
	}
}

/**
 * THEME OUTPUT STYLES
 * 
 * Outputs a list of HTML Link tags with URLs to all registered stylesheet files.
 * This function will echo out the result.
 */
function theme_output_styles() {
	global $theme_styles;
	
	if( is_array( $theme_styles ) && count( $theme_styles ) > 0 ) {
		foreach( $theme_styles as $theme_style ) {
			if( isset( $theme_style['path'] ) && isset( $theme_style['id'] ) ) {
				echo "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $theme_style['path'] . ( isset( $theme_style['ver'] ) ? "?ver=" . $theme_style['ver'] : "" ) . "\" id=\"" . $theme_style['id'] . "-css\">\n";
			}
		}
	}
}

/**
 * THEME REGISTER SCRIPT
 * 
 * Register a JavaScript file for loading later. This will ensure only one of each JavaScript file is loaded.
 * Content-Type of file at path must be either text/javascript or application/javascript.
 * 
 * TODO: Add dependency support.
 * 
 * @param String $id The unique ID to register the script with, which also appears within the id attribute of the script tag as "$id-js".
 * @param String $path The URL path to the JavaScript file to load.
 * @param String $ver (Optional) A version number to append to the URL as a query string ?ver=12345.
 */
function theme_register_script( String $id, String $path, String $ver = "" ) {
	global $theme_scripts;
	
	if( is_array( $theme_scripts ) && ! array_key_exists( $id, $theme_scripts ) ) {
	    if( gethostbyaddr( parse_url( $path, PHP_URL_HOST ) ) ) {
			$headers = get_headers( $path, 1 );
			
			if( array_key_exists( 'Content-Type', $headers ) && ( 'text/javascript' == $headers['Content-Type'] || 'application/javascript' == $headers['Content-Type'] ) ) {
				$theme_scripts[ $id ][ 'id' ] = $id;
				$theme_scripts[ $id ][ 'path' ] = $path;
				$theme_scripts[ $id ][ 'type' ] = $headers['Content-Type'];
				
				if( $ver != "" )
					$theme_scripts[ $id ][ 'ver' ] = $ver;
			}
		}
	}
}

/**
 * THEME OUTPUT SCRIPTS
 * 
 * Outputs a list of HTML Script tags with URLs to all registered JavaScript files.
 * This function will echo out the result.
 */
function theme_output_scripts() {
	global $theme_scripts;
	
	if( is_array( $theme_scripts ) && count( $theme_scripts ) > 0 ) {
		foreach( $theme_scripts as $theme_script ) {
			if( isset( $theme_script['path'] ) && isset( $theme_script['id'] ) ) {
				echo "\n<script type=\"" . $theme_script['type'] . "\" src=\"" . $theme_script['path'] . ( isset( $theme_script['ver'] ) ? "?ver=" . $theme_script['ver'] : "" ) . "\" id=\"" . $theme_script['id'] . "-js\"></script>\n";
			}
		}
	}
}

/**
 * THEME INITIALISATION
 * 
 * Initialises the current theme.
 */
function theme_init() {
	add_action( 'theme_head', 'theme_meta_generator' );
	add_action( 'theme_body_close', 'theme_attribution' );
	
	theme_load_part( 'functions' );
	
	theme_do_supports();
	
	add_action( 'theme_head', 'theme_output_styles' );
	add_action( 'theme_body_close', 'theme_output_scripts' );
	
	$path = isset( $_GET['path'] ) ? $_GET['path'] : "/";
	$request = substr( $path, 1 );
	
	global $request_path;
	$request_path = $request;

	$requested_path = theme_file_path( $request );

	if( '/' != $path && $requested_path ) {
		theme_load_part( $request );
	} else {
		if( '/' != $path && ! $requested_path ) {
			global $error_404;
			$error_404 = true;
			header("HTTP/1.0 404 Not Found");
		}

		if( theme_file_path( '404' ) && is_error_404() )
			theme_load_part( '404' );
		else
			theme_load_part( 'index' );
	}
}

/**
 * THEME META GENERATOR
 * 
 * Outputs a meta generator tag with DH-CMS info.
 */
function theme_meta_generator() {
	echo "\n<meta name=\"generator\" content=\"DH-CMS\">\n";
}

/**
 * THEME ATTRIBUTION
 * 
 * A little mention to the content management software the site is powered by.
 * 
 * You can disable it by removing the "theme_attribution" action from "theme_body_close".
 * remove_action( 'theme_body_close', 'theme_attribution' );
 */
function theme_attribution() {
	echo "<p style=\"padding: 20px; text-align: center; font-size: 12px;\">Proudly powered by <a href=\"https://github.com/DavidHunterScot/DH-CMS\" target=\"_blank\">DH-CMS</a>.</p>\n";
}

/**
 * THEME HEAD
 * 
 * Perform the "theme_head" action.
 */
function theme_head() {
	do_action( 'theme_head' );
}

/**
 * THEME BODY OPEN
 * 
 * Perform the "theme_body_open" action.
 * Perform the "theme_body_top" action.
 */
function theme_body_open() {
	do_action( 'theme_body_open' );
	do_action( 'theme_body_top' );
}

/**
 * THEME BODY TOP
 * 
 * Alias of theme_body_open().
 */
function theme_body_top() {
	theme_body_open();
}

/**
 * THEME BODY CLOSE
 * 
 * Perform the "theme_body_close" action.
 * Perform the "theme_body_bottom" action.
 */
function theme_body_close() {
	do_action( 'theme_body_close' );
	do_action( 'theme_body_bottom' );
}

/**
 * THEME BODY_BOTTOM
 * 
 * Alias of theme_body_close().
 */
function theme_body_bottom() {
	theme_body_close();
}

/**
 * THEME URL
 * 
 * Generate the full URL to the requested file path.
 * 
 * @param String $path (Optional) The path relative to the theme root directory.
 * @return String The full generated URL based on the $path, or the full URL to the theme root directory.
 */
function theme_url( String $path = "" ) {
	if( substr( $path, 0, 1 ) == "/" )
		$path = substr( $path, 1 );
	
	return url( "/dh-content/themes/" . config( 'theme' ) . "/" . $path );
}

/**
 * THEME META
 * 
 * Outputs HTML meta tags for the requested data.
 * 
 * @param String $data (Optional) The data to output a meta tag for. Currently supports "charset" and "viewport". Defaults to outputting both if left blank.
 */
function theme_meta( String $data = "" ) {
	if( $data == "" ) {
		echo "\n";
		theme_meta( "charset" );
		echo "\n";
		theme_meta( "viewport" );
		echo "\n";
	}
	
	if( $data == "charset" )
		echo '<meta charset="' . config( 'charset' ) . '">';
	if( $data == "viewport" )
		echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
}

/**
 * THEME STYLESHEET URL
 * 
 * Generates the full URL to the theme.css file for the theme.
 * 
 * @return String The full URL to theme.css.
 */
function theme_stylesheet_url() {
	return theme_url( 'theme.css' );
}

/**
 * THEME GENERATE PAGE TITLE
 * 
 * Generates and returns a page title based on the requested path and optionally append the name in config.
 * 
 * @param String $landing_page_title (Optional) A title for the home/landing page of the site/app.
 * @param bool $append_config_name (Optional) Whether to automatically append the name as defined in config.
 * @return String The generated page title.
 */
function theme_generate_page_title( String $landing_page_title = "", bool $append_config_name = false ) {
	global $request_path;

	$request_path = str_replace( [ '-', '_', '/' ], [ ' ', ' ', ' :: ' ], $request_path );

	$page_title = "404 Not Found" . ( $append_config_name ? " :: " . config( "name" ) : "" );

	if( ! is_not_found() && "" != $request_path ) {
		$page_title = $request_path . ( $append_config_name ? " :: " . config( "name" ) : "" );
	}

	if( ! is_not_found() && "" == $request_path ) {
		if( $landing_page_title ) {
			$page_title = $landing_page_title . ( $append_config_name ? " :: " . config( "name" ) : "" );
		} else {
			$page_title = config( "name" );
		}
	}

	return ucwords( $page_title );
}

/**
 * THEME TITLE TAG
 * 
 * Echos HTML title tags with the site name as defined in config file as "name".
 */
function theme_title_tag() {
	echo "<title>" . theme_generate_page_title( "", true ) . "</title>";
}

/**
 * THEME ADD SUPPORT
 * 
 * Adds support for a theme feature.
 * 
 * @param String $support A string for the feature to add support for.
 */
function theme_add_support( String $support ) {
	global $theme_supports;
	
	$theme_supports[] = $support;
}

function theme_do_supports() {
    global $theme_supports;
    
    foreach( $theme_supports as $support ) {
        if( 'title-tag' == $support ) {
            add_action( 'theme_head', 'theme_title_tag' );
        }
    }
}

/**
 * THEME ADD BODY CLASS
 * 
 * Adds a body class to be loaded into the theme body tag.
 * 
 * @param String $body_class The class to add. Multiple classes can be provided separated by a space.
 */
function theme_add_body_class( String $body_class ) {
	global $theme_body_classes;

	if( $body_class ) {
		$body_class = explode( " ", $body_class );

		foreach( $body_class as $bc ) {
			$theme_body_classes[ $bc ] = $bc;
		}
	}
}

/**
 * THEME BODY CLASS
 * 
 * Performs the theme_body_class action to load in any class names for use in the body tag.
 * 
 * Returns the list of classes as a space separeted string.
 * An HTML class attribute will also be returned unless the optional string is provided.
 * 
 * An empty string will be provided if class list is empty.
 * 
 * @param String $raw_outout (Optional) To be provided only if wishing raw output.
 * @return String HTML class attribute prefixed by space with space separated class list, or raw space-separated class list, or empty string.
 */
function theme_body_class( String $raw_output = "" ) {
	do_action( "theme_body_class" );

	global $theme_body_classes;

	$list = join( " ", $theme_body_classes );

	if( $list )
		return $raw_output ? $list : " class=\"" . $list . "\"";
	else
		return "";
}

/**
 * IS ERROR 404
 * 
 * Is the current request resulting in a 404 Not Found error?
 * 
 * @return boolean TRUE or FALSE indication of 404 error.
 */
function is_error_404() {
	global $error_404;
	return $error_404;
}

/**
 * IS NOT FOUND
 * 
 * Is the current request resulting in a 404 Not Found error?
 * 
 * Alias of is_error_404().
 * 
 * @return boolean TRUE or FALSE indication of 404 error.
 */
function is_not_found() {
	return is_error_404();
}
