<?php

$actions = array();

/**
 * ADD ACTION
 * 
 * Prepares an action to be performed.
 * 
 * @param String $name The name of the action to add.
 * @param String $function A callback function for the action to perform.
 */
function add_action( String $name, String $function ) {
	global $actions;
	
	if( function_exists( $function ) && is_callable( $function ) )
		$actions[ $name ][] = $function;
}

/**
 * DO ACTION
 * 
 * Perform an action and call all callback functions.
 * 
 * @param String $name The name of the action to perform.
 */
function do_action( String $name ) {
	global $actions;
	
	if( action_exists( $name ) ) {
		foreach( $actions[ $name ] as $action ) {
			if( function_exists( $action ) && is_callable( $action ) ) {
				call_user_func( $action );
			}
		}
	}
}

/**
 * ACTION EXISTS
 * 
 * Determine if an action exists based on the name and string as a callback function.
 * 
 * @param String $name The name of the action to check.
 * @param String $function (Optional) The callback function to check or blank to check if any exists.
 * @return boolean A TRUE or FALSE indication of existence.
 */
function action_exists( String $name, String $function = "" ) {
	global $actions;
	
	if( array_key_exists( $name, $actions ) ) {
		if( is_array( $actions[ $name ] ) && count( $actions[ $name ] ) > 0 && $function != "" ) {
			foreach( $actions[ $name ] as $action ) {
				if( $action == $function ) {
					return true;
				}
			}
		} elseif( $function == "" ) {
			return true;
		}
	}
	
	return false;
}

/**
 * REMOVE ACTION
 * 
 * Remove an action that has already been registered.
 * 
 * @param String $name The name of the action to remove.
 * @param String $function (Optional) The callback function to remove from the action or blank for all.
 */
function remove_action( String $name, String $function = "" ) {
	global $actions;
	
	if( action_exists( $name, $function ) ) {
		if( $function != null ) {
			for( $a = 0; $a < count( $actions[ $name ] ); $a++ ) {
				if( $actions[ $name ][ $a ] == $function ) {
					unset( $actions[ $name ][ $a ] );
				}
			}
		} else {
			unset( $actions[ $name ] );
		}
	}
}
