<?php
/*
 * Template Name: Membership Page
 */

	get_header();
	the_post();
?>
<div id="content">
	<div class="inner-content-wrapper non-booking">
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
