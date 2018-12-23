<?php
/*
 * Template Name: News Page
 */
	get_header(); 
	the_post();
?>
<div id="internal" class="max non-booking">
	<div id="content">
		<div class="inner-content-wrapper non-booking">
			<h1><?php echo get_the_title();?></h1>
			<?php 
				$content = get_the_content();
				echo apply_filters('the_content', $content);
			?>
			<div id="gallery_wrapper">
				<h2>MEMBERS PHOTO GALLERY</h2>
				<p>
					Click a thumbnail to open the specific photo gallery. Navigate to the next/previous image by using the arrow keys or alternatively click on the right or left of the image.
				</p>
				<?php
					// check if the repeater field has rows of data
					if( have_rows('photo_gallery') ):
						// loop through the rows of data
						$count = 0;
						while ( have_rows('photo_gallery') ) : the_row();
							$images = get_sub_field('gal_images');
						?>
						<div class="member_gallery_tab">
							<a class="hideshow" value="hide/show"><div class="plus_minus"></div></a> <b><?php the_sub_field('gallery_name'); ?></b> Click image to launch gallery
								<span><b>Added <?php the_sub_field('gallery_date') ?></b></span>
							
							<div class="member_gallery_images" id="member_gallery_images_<?php echo $count; ?>" style="display: none">
								
								<?php if( $images ): ?>
									<?php foreach( $images as $image ): ?>
											<a href="<?php echo $image['url']; ?>" rel="lightbox[<?php the_sub_field('gallery_name') ?>]">
												<img src="<?php echo $image['sizes']['gallery-thumb']; ?>" alt="<?php echo $image['alt']; ?>" />
											</a>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>
					<?php 
					$count += 1;
					endwhile;	else :
						// no rows found
				endif; ?>
			</div>
		</div>
	</div>
</div>

<?php
	get_footer();
?>
