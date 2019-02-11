<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>


	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
            <?php get_sidebar('404'); ?>
			<header class="page-header">
				<h1 class="page-title"><?php _e( 'Oops! That page can\'t be found.', 'psl' ); ?></h1>
			</header>

			<div class="page-content">
                <p>Try the search on the left, or <b>login to view member pages.</b></p>

			</div><!-- .page-content -->

		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();
