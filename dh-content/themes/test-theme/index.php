<!DOCTYPE html>
<html <?php lang_attr(); ?>>
	<head>
		<?php theme_meta(); ?>
		
		<?php theme_head(); ?>
	</head>
	
	<body>
		<?php theme_body_top(); ?>
		
		<p>This is the index file of <?php echo theme( 'name' ); ?> by <?php echo theme( 'author' ); ?>.</p>
		
		<?php theme_body_bottom(); ?>
	</body>
</html>