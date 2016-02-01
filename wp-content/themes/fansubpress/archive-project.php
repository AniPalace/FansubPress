<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Fifteen
 */

get_header('single'); ?>
	<h1 class="container single-entry-title">
					<?php
							_e( 'Projectek', 'fifteen' );
					?>
				</h1>
			<div id="content" class="site-content container row clearfix clear">
	<div class="container col-md-12">
	<div id="primary-main" class="content-area col-md-12">
		<main id="main" class="site-main row container" role="main">


		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', 'project');
				?>

			<?php endwhile; ?>

			<?php fifteen_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'no-results', 'archive' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</main><!-- #primary-main -->

<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>
