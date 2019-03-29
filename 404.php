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
                <?php
                if( is_user_logged_in() ){
                    ?>
                    <h1 class="page-title"><?php _e( 'Oops! That page can\'t be found.', 'psl' ); ?></h1>
                    <?php
                } else {
                    ?>
                    <h1 class="page-title"><?php _e( 'Some pages require member login to view', 'psl' ); ?></h1>
                    <?php
                }?>
			</header>

			<div class="page-content">
                <p>Try the search on the left<?= is_user_logged_in() ? ""  : ", or <b>login to view member pages"?>.</b></p>

			</div><!-- .page-content -->

		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();
