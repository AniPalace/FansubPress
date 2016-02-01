<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Fifteen
 */

get_header('single'); ?>

	<h1 class="container single-entry-title"><?php printf( __( 'Search Results for: %s', 'fifteen' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
	<div id="content" class="site-content container row clearfix clear">
	<div class="container col-md-12">
	<section id="primary" class="content-area col-md-8">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>
			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'search' ); ?>

			<?php endwhile; ?>

			<?php fifteen_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'no-results', 'search' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>