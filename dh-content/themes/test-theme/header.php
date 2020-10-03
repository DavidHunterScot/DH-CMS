<!DOCTYPE html>
<html <?php lang_attr(); ?>>
	<head>
		<?php theme_meta(); ?>
		
		<?php theme_head(); ?>
	</head>
	
	<body>
		<?php theme_body_top(); ?>

		<h1><?php echo config( 'name' ); ?></h1>
    	<h2><?php echo theme_generate_page_title( "home" ); ?></h2>
