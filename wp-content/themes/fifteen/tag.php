<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package fifteen
 */

get_header('single'); ?>

<h1 class="container single-entry-title">
				<?php
					if ( is_tag() ) :
						single_tag_title();
					endif;
						?>
		</h1>
		<div id="content" class="site-content container row clearfix clear">
	<div class="container col-md-12">
	
<div id="primary-main" class="content-area col-md-12">
		<main id="main" class="site-main row container" role="main">

		<?php if ( have_posts() ) : ?>

			<?php /* Start the Loop */ $ink_count = 0; $ink_row_count=0 ?>
			<?php while ( have_posts() ) : the_post(); 
				 if ($ink_count == 0 ) {echo "<div class='row-".$ink_row_count." row'>";}
			?>
			

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', 'home' );
				?>

			<?php 
				if ($ink_count == 2 )
					{
						echo "</div>";
						$ink_count=0;
						$ink_row_count++;
					}
				else {	
					$ink_count++;
				}
				
				endwhile; 
			?>

			<?php fifteen_pagination(); ?>

		<?php else : ?>

			<?php get_template_part( 'no-results', 'index' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</div><!-- #primary-main -->

<?php get_sidebar('footer'); ?>
<?php get_footer(); ?>