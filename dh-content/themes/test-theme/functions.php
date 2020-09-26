<?php

function testtheme_styles() {
	theme_register_style( 'theme', theme_stylesheet_url(), filemtime( theme_file_path( 'theme', 'css' ) ) );
	theme_register_style( 'stylesheet', theme_url( 'stylesheet.css' ), filemtime( theme_file_path( 'stylesheet', 'css' ) ) );
}

add_action( 'theme_head', 'testtheme_styles' );


function testtheme_body_open() {
	echo "<h1>" . config( 'name' ) . "</h1>";
}

add_action( 'theme_body_open', 'testtheme_body_open' );


function testtheme_body_close() {
	echo "<p>Copyright &copy; " . config( 'name' ) . " " . date('Y') . "</p>";
}

add_action( 'theme_body_close', 'testtheme_body_close' );

remove_action( 'theme_body_close', 'theme_attribution' );


theme_add_support( 'title-tag' );

