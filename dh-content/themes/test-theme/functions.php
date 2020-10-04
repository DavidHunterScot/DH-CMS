<?php

theme_add_support( 'title-tag' );


remove_action( 'theme_head', 'theme_meta_generator' );
remove_action( 'theme_body_close', 'theme_attribution' );


function testtheme_styles() {
	theme_register_style( 'theme', theme_stylesheet_url(), filemtime( theme_file_path( 'theme', 'css' ) ) );
}

add_action( 'theme_head', 'testtheme_styles' );


function testtheme_scripts() {
	theme_register_script( 'test', theme_url( 'test.js' ), filemtime( theme_file_path( 'test', 'js' ) ) );
}

add_action( 'theme_body_close', 'testtheme_scripts' );


function testtheme_body_class() {
	theme_add_body_class( 'testtheme_body_class' );
}

add_action( 'theme_body_class', 'testtheme_body_class' );
