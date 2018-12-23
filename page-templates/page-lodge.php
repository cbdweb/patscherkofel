<?php
/*
 * Template Name: Lodge Page
 */
	get_header();
	the_post();
?>
<div id="content">
	<?php get_sidebar('lodge'); ?>
	<div id="internal" class="main lodge-page non-booking">
		<h1><?php echo get_the_title();?></h1>
		
		<?php 
			$content = get_the_content();
			echo apply_filters('the_content', $content);
		?>
	</div>
</div>
<?php
	get_footer();
?>
