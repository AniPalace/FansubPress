<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Fifteen
 */

get_header('single'); ?>

	<h1 class=" container single-entry-title"><?php the_title(); ?></h1>
	<div id="content" class="site-content container row clearfix clear">
	<div class="container col-md-12">
	<div id="primary" class="content-area col-md-8">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'single' ); ?>

			<?php if ( (of_get_option('next-prev-posts') != 0 ) ) {
				fifteen_content_nav( 'nav-below' ); 
				}
			?>

			<?php
				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || '0' != get_comments_number() )
					comments_template();
			?>

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>